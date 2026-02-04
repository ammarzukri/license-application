<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\LicenseApplication;
use App\Models\LicenseDocument;

class LicenseApplicationController extends Controller
{
    protected function ensureAdmin(): void
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
    }

    public function create()
    {
        $existingApplication = LicenseApplication::where('user_id', auth()->id())->first();
        if ($existingApplication) {
            return redirect()->route('license.status');
        }

        return Inertia::render('lesen/Create');
    }

    public function status()
    {
        $application = LicenseApplication::with('licenseTypes')
            ->where('user_id', auth()->id())
            ->latest('id')
            ->first();

        return Inertia::render('lesen/Status', [
            'application' => $application,
            'licenseTypes' => $application?->licenseTypes ?? [],
        ]);
    }

    public function startPayment(Request $request)
    {
        $application = LicenseApplication::where('user_id', auth()->id())
            ->latest('id')
            ->first();

        if (! $application) {
            return redirect()->route('license.status')->with('payment', [
                'status' => 'error',
                'message' => 'Tiada rekod permohonan ditemui.',
            ]);
        }

        if ($application->status !== 'Diluluskan') {
            return redirect()->route('license.status')->with('payment', [
                'status' => 'error',
                'message' => 'Permohonan belum diluluskan untuk pembayaran.',
            ]);
        }

        if ($application->payment_status === 'Berjaya') {
            return redirect()->route('license.status')->with('payment', [
                'status' => 'success',
                'message' => 'Pembayaran telah berjaya.',
            ]);
        }

        $amountInCents = 10000;
        $baseUrl = $this->toyyibpayBaseUrl();

        $secretKey = config('services.toyyibpay.key') ?? env('TOYYIBPAY_API_KEY');
        $categoryCode = config('services.toyyibpay.category_code') ?? env('TOYYIBPAY_CATEGORY_CODE');

        if (! $secretKey || ! $categoryCode) {
            return redirect()->route('license.status')->with('payment', [
                'status' => 'error',
                'message' => 'Tetapan pembayaran tidak lengkap. Sila hubungi pentadbir.',
            ]);
        }

        $response = Http::asForm()->post($baseUrl.'/index.php/api/createBill', [
            'userSecretKey' => $secretKey,
            'categoryCode' => $categoryCode,
            'billName' => 'Bayaran Lesen',
            'billDescription' => 'Bayaran lesen perniagaan',
            'billPriceSetting' => 1,
            'billPayorInfo' => 1,
            'billAmount' => $amountInCents,
            'billReturnUrl' => route('license.payment.return'),
            'billCallbackUrl' => route('license.payment.callback'),
            'billExternalReferenceNo' => (string) $application->id,
            'billTo' => $application->name ?? 'Pemohon',
            'billEmail' => $application->email ?? auth()->user()?->email ?? 'no-reply@example.com',
            'billPhone' => $application->phone_number ?? '',
        ]);

        if (! $response->successful()) {
            return redirect()->route('license.status')->with('payment', [
                'status' => 'error',
                'message' => 'Gagal memulakan pembayaran. Sila cuba lagi.',
            ]);
        }

        $payload = $response->json();
        $billCode = data_get($payload, '0.BillCode') ?? data_get($payload, 'BillCode');

        if (! $billCode) {
            return redirect()->route('license.status')->with('payment', [
                'status' => 'error',
                'message' => 'Gagal memulakan pembayaran. Sila cuba lagi.',
            ]);
        }

        $application->update([
            'payment_status' => 'Dalam Proses',
            'payment_amount' => $amountInCents,
            'payment_billcode' => $billCode,
            'payment_attempted_at' => now(),
        ]);

        $paymentUrl = $baseUrl.'/'.$billCode;

        if ($request->header('X-Inertia')) {
            return Inertia::location($paymentUrl);
        }

        return redirect()->away($paymentUrl);
    }

    public function handlePaymentReturn(Request $request)
    {
        $billCode = $request->query('billcode') ?? $request->query('billCode');

        if (! $billCode) {
            return redirect()->route('license.status')->with('payment', [
                'status' => 'error',
                'message' => 'Maklumat pembayaran tidak ditemui.',
            ]);
        }

        $application = LicenseApplication::where('payment_billcode', $billCode)
            ->where('user_id', auth()->id())
            ->latest('id')
            ->first();

        if (! $application) {
            return redirect()->route('license.status')->with('payment', [
                'status' => 'error',
                'message' => 'Rekod pembayaran tidak ditemui.',
            ]);
        }

        $paymentSuccess = $this->syncToyyibpayStatus($billCode, $application);

        return redirect()->route('license.status')->with('payment', [
            'status' => $paymentSuccess ? 'success' : 'error',
            'message' => $paymentSuccess
                ? 'Pembayaran berjaya. Terima kasih.'
                : 'Pembayaran gagal. Sila cuba lagi.',
        ]);
    }

    public function handlePaymentCallback(Request $request)
    {
        $billCode = $request->input('billcode') ?? $request->input('billCode');

        if (! $billCode) {
            return response()->json(['status' => 'missing-billcode'], 400);
        }

        $application = LicenseApplication::where('payment_billcode', $billCode)
            ->latest('id')
            ->first();

        if (! $application) {
            return response()->json(['status' => 'not-found'], 404);
        }

        $this->syncToyyibpayStatus($billCode, $application);

        return response()->json(['status' => 'ok']);
    }

    protected function syncToyyibpayStatus(string $billCode, LicenseApplication $application): bool
    {
        $baseUrl = $this->toyyibpayBaseUrl();

        $response = Http::asForm()->post($baseUrl.'/index.php/api/getBillTransactions', [
            'billCode' => $billCode,
        ]);

        $payload = $response->json();
        $paymentStatus = data_get($payload, '0.billpaymentStatus')
            ?? data_get($payload, 'billpaymentStatus');

        $isSuccess = in_array((string) $paymentStatus, ['1', 'success', 'SUCCESS'], true);

        $application->update([
            'payment_status' => $isSuccess ? 'Berjaya' : 'Gagal',
            'payment_paid_at' => $isSuccess ? now() : null,
        ]);

        return $isSuccess;
    }

    protected function toyyibpayBaseUrl(): string
    {
        return config('services.toyyibpay.sandbox') ? 'https://dev.toyyibpay.com' : 'https://toyyibpay.com';
    }

    public function adminIndex()
    {
        $this->ensureAdmin();

        $applications = LicenseApplication::query()
            ->latest('id')
            ->get([
                'id',
                'name',
                'ic_no',
                'company_name',
                'status',
                'created_at',
            ]);

        return Inertia::render('admin/LicenseApplications', [
            'applications' => $applications,
        ]);
    }

    public function adminShow(LicenseApplication $application)
    {
        $this->ensureAdmin();

        $application->load(['licenseTypes', 'advertisementInfos', 'documents']);

        return Inertia::render('admin/LicenseApplicationShow', [
            'application' => $application,
        ]);
    }

    public function approve(LicenseApplication $application)
    {
        $this->ensureAdmin();

        $application->update([
            'status' => 'Diluluskan',
        ]);

        return redirect()->back();
    }

    public function reject(LicenseApplication $application)
    {
        $this->ensureAdmin();

        $application->update([
            'status' => 'Ditolak',
        ]);

        return redirect()->back();
    }

    public function adminDocument(LicenseDocument $document)
    {
        $this->ensureAdmin();

        if (!Storage::disk('local')->exists($document->file_path)) {
            abort(404);
        }

        return Storage::disk('local')->response($document->file_path);
    }

    public function store(Request $request)
    {
        $request->validate([
            'pbt_name' => ['nullable', 'string'],
            'applicant_info' => ['required', 'array'],
            'company_info' => ['required', 'array'],
            'license_type' => ['nullable', 'array'],
            'advertisement_info' => ['nullable', 'array'],
            'documents' => ['nullable', 'array'],
            'documents.*.document_type' => ['nullable', 'string'],
            'documents.*.file' => ['nullable', 'file', 'max:10240'],
        ]);

        DB::transaction(function () use ($request) {
            $applicant = $request->input('applicant_info', []);
            $company = $request->input('company_info', []);

            $licenseApplication = LicenseApplication::create([
                'name' => $applicant['name'] ?? null,
                'user_id' => auth()->id(),
                'pbt_name' => $request->input('pbt_name'),
                'ic_no' => $applicant['ic_no'] ?? null,
                'status' => 'Dalam Proses',
                'birth_date' => $applicant['birth_date'] ?? null,
                'birth_place' => $applicant['birth_place'] ?? null,
                'gender' => $applicant['gender'] ?? null,
                'citizenship' => $applicant['citizenship'] ?? null,
                'religion' => $applicant['religion'] ?? null,
                'ethnicity' => $applicant['ethnicity'] ?? null,
                'maritial_status' => $applicant['marital_status'] ?? null,
                'occupation' => $applicant['occupation'] ?? null,
                'income' => $applicant['income'] ?? null,
                'home_address' => $applicant['home_address'] ?? null,
                'postcode' => $applicant['postcode'] ?? null,
                'state' => $applicant['state'] ?? null,
                'district' => $applicant['district'] ?? null,
                'phone_number' => $applicant['phone_number'] ?? null,
                'email' => $applicant['email'] ?? null,
                'company_name' => $company['company_name'] ?? null,
                'company_address' => $company['company_address'] ?? null,
                'company_postcode' => $company['company_postcode'] ?? null,
                'company_state' => $company['company_state'] ?? null,
                'company_district' => $company['company_district'] ?? null,
                'company_phone' => $company['company_phone'] ?? null,
                'company_registration_number' => $company['company_registration_number'] ?? null,
                'company_registration_date' => $company['company_registration_date'] ?? null,
                'company_registration_expiry_date' => $company['company_registration_expiry_date'] ?? null,
                'employee_malay' => $company['employee_malay'] ?? null,
                'employee_chinese' => $company['employee_chinese'] ?? null,
                'employee_indian' => $company['employee_indian'] ?? null,
                'employee_others' => $company['employee_others'] ?? null,
                'company_operation_start' => $company['company_operation_start'] ?? null,
                'company_operation_end' => $company['company_operation_end'] ?? null,
                'company_address_hq' => $company['company_address_hq'] ?? null,
                'company_postcode_hq' => $company['company_postcode_hq'] ?? null,
                'company_state_hq' => $company['company_state_hq'] ?? null,
                'company_district_hq' => $company['company_district_hq'] ?? null,
                'company_phone_hq' => $company['company_phone_hq'] ?? null,
            ]);

            $licenseTypes = $request->input('license_type', []);
            foreach ($licenseTypes as $row) {
                if (!array_filter($row ?? [])) {
                    continue;
                }
                $licenseApplication->licenseTypes()->create([
                    'aktiviti' => $row['aktiviti'] ?? null,
                    'keluasan' => $row['keluasan'] ?? null,
                    'unit_bilik' => $row['unit_bilik'] ?? null,
                ]);
            }

            $advertisements = $request->input('advertisement_info', []);
            foreach ($advertisements as $row) {
                if (!array_filter($row ?? [])) {
                    continue;
                }
                $licenseApplication->advertisementInfos()->create([
                    'type' => $row['type'] ?? null,
                    'structure' => $row['structure'] ?? null,
                    'length' => $row['length'] ?? null,
                    'width' => $row['width'] ?? null,
                    'number_of_ads' => $row['number_of_ads'] ?? null,
                ]);
            }

            $documents = $request->input('documents', []);
            foreach ($documents as $index => $doc) {
                $file = $request->file("documents.$index.file");
                if (!$file) {
                    continue;
                }

                $path = $file->store('license_documents');

                $licenseApplication->documents()->create([
                    'document_type' => $doc['document_type'] ?? null,
                    'file_path' => $path,
                    'upload_at' => now(),
                ]);
            }
        });

        return redirect()->route('license.status');
    }
}
