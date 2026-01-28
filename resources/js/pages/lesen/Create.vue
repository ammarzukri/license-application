<script setup lang="ts">
import { ref, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { dashboard } from '@/routes'
import { apply as licenseApply } from '@/routes/license'
import type { BreadcrumbItem } from '@/types'

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: dashboard().url },
  { title: 'Permohonan Lesen', href: licenseApply().url },
]

const step = ref(1)
const isSubmitting = ref(false)
const submitSuccess = ref('')
const submitError = ref('')

const stepTitles = [
  'Maklumat Pemohon',
  'Maklumat Perniagaan / Syarikat',
  'Maklumat Iklan / Papan Tanda',
  'Dokumen Sokongan',
  'Pengesahan',
]

const form = ref({
  applicant_info: {
    name: '',
    ic_no: '',
    birth_date: '',
    birth_place: '',
    gender: '',
    ethnicity: '',
    religion: '',
    citizenship: '',
    marital_status: '',
    occupation: '',
    income: '',
    home_address: '',
    postcode: '',
    state: '',
    district: '',
    phone_number: '',
    email: '',
  },
  company_info: {
    name: '',
    ic: '',
    company_name: '',
    company_address: '',
    company_postcode: '',
    company_state: '',
    company_district: '',
    company_phone: '',
    company_registration_number: '',
    company_registration_date: '',
    company_registration_expiry_date: '',
    license_type: [
      { aktiviti: '', keluasan: '', unit_bilik: '' }
    ],
    employee_malay: '',
    employee_chinese: '',
    employee_indian: '',
    employee_others: '',
    company_operation_start: '',
    company_operation_end: '',
    company_address_hq: '',
    company_postcode_hq: '',
    company_state_hq: '',
    company_district_hq: '',
    company_phone_hq: '',
    company_license_type: '',
    company_license_i: '',
    company_license_ii: '',
    company_license_iii: '',
  },
  advertisment_info: {
    address: '',
    dynamic_table_rows: [
      { type: '', structure: '', length: '', width: '', number_of_ads: '' },
    ],
  },
  declaration: {
    agree: false,
  },
  document1File: null as File | null,
  document1Name: '',
  document2File: null as File | null,
  document2Name: '',
  document3File: null as File | null,
  document3Name: '',
  document4File: null as File | null,
  document4Name: '',
  document5File: null as File | null,
  document5Name: '',
  document6File: null as File | null,
  document6Name: '',
  document7File: null as File | null,
  document7Name: '',
  document8File: null as File | null,
  document8Name: '',
  document9File: null as File | null,
  document9Name: '',
  document10File: null as File | null,
  document10Name: '',
})

const religionSelection = ref(form.value.applicant_info.religion)
const customReligion = ref('')
const ethnicitySelection = ref(form.value.applicant_info.ethnicity)
const customEthnicity = ref('')

watch([religionSelection, customReligion], ([selection, custom]) => {
  if (selection === 'Lain-lain') {
    form.value.applicant_info.religion = custom
  } else {
    form.value.applicant_info.religion = selection
    if (customReligion.value) customReligion.value = ''
  }
})

watch([ethnicitySelection, customEthnicity], ([selection, custom]) => {
  if (selection === 'Lain-lain') {
    form.value.applicant_info.ethnicity = custom
  } else {
    form.value.applicant_info.ethnicity = selection
    if (customEthnicity.value) customEthnicity.value = ''
  }
})

function nextStep() {
  // Allow progressing through steps without completing fields
  if (step.value < 5) step.value++
}

function prevStep() {
  if (step.value > 1) step.value--
}

function submitForm() {
  submitSuccess.value = ''
  submitError.value = ''

  const documentTypes = [
    'memorandum',
    'pelan_lokasi',
    'pelan_lantai',
    'surat_perjanjian',
    'salinan_geran_tanah',
    'sijil_menduduki_bangunan',
    'gambar_pemohon',
    'salinan_kad_pengenalan_pemohon',
    'senarai_nama_semua_pengendali_makanan',
    'carta_proses_pengeluaran',
  ]

  const documentFiles = [
    form.value.document1File,
    form.value.document2File,
    form.value.document3File,
    form.value.document4File,
    form.value.document5File,
    form.value.document6File,
    form.value.document7File,
    form.value.document8File,
    form.value.document9File,
    form.value.document10File,
  ]

  const documents = documentTypes
    .map((document_type, index) => ({
      document_type,
      file: documentFiles[index] || null,
    }))
    .filter((doc) => doc.file)

  const { license_type, ...companyInfo } = form.value.company_info

  const payload = {
    applicant_info: form.value.applicant_info,
    company_info: companyInfo,
    license_type,
    advertisement_info: form.value.advertisment_info.dynamic_table_rows,
    documents,
  }

  router.post(licenseApply().url, payload, {
    forceFormData: true,
    preserveScroll: true,
    onStart: () => {
      isSubmitting.value = true
    },
    onFinish: () => {
      isSubmitting.value = false
    },
    onSuccess: () => {
      submitSuccess.value = 'Permohonan berjaya dihantar.'
    },
    onError: () => {
      submitError.value = 'Permohonan gagal dihantar. Sila semak semula borang.'
    },
  })
}

function addAdvertismentRow() {
  form.value.advertisment_info.dynamic_table_rows.push({ type: '', structure: '', length: '', width: '', number_of_ads: '' })
}

function removeAdvertismentRow(index: number) {
  if (form.value.advertisment_info.dynamic_table_rows.length > 1) {
    form.value.advertisment_info.dynamic_table_rows.splice(index, 1)
  }
}

function addLicenseTypeRow() {
  form.value.company_info.license_type.push({ aktiviti: '', keluasan: '', unit_bilik: '' })
}

function removeLicenseTypeRow(index: number) {
  if (form.value.company_info.license_type.length > 1) {
    form.value.company_info.license_type.splice(index, 1)
  }
}

const advertismentOptions1 = [
  'Bersinar',
  'Tidak Bersinar',
]

const advertismentOptions2 = [
  'Pada Bangunan',
  'Menganjur',
  'Tepi Jalan',
]

function handleDocumentChange(index: number, event: Event) {
  const input = event.target as HTMLInputElement
  const file = input.files && input.files[0] ? input.files[0] : null
  const name = file ? file.name : ''
  switch (index) {
    case 1:
      form.value.document1File = file
      form.value.document1Name = name
      break
    case 2:
      form.value.document2File = file
      form.value.document2Name = name
      break
    case 3:
      form.value.document3File = file
      form.value.document3Name = name
      break
    case 4:
      form.value.document4File = file
      form.value.document4Name = name
      break
    case 5:
      form.value.document5File = file
      form.value.document5Name = name
      break
    case 6:
      form.value.document6File = file
      form.value.document6Name = name
      break
    case 7:
      form.value.document7File = file
      form.value.document7Name = name
      break
    case 8:
      form.value.document8File = file
      form.value.document8Name = name
      break
    case 9:
      form.value.document9File = file
      form.value.document9Name = name
      break
    case 10:
      form.value.document10File = file
      form.value.document10Name = name
      break
  }
}
</script>

<template>
  <Head title="Permohonan Lesen" />

  <AppLayout :breadcrumbs="breadcrumbs">

    <div class="w-full h-full flex flex-col p-6 bg-white rounded-xl shadow">

      <!-- Step Indicator -->
      <div class="flex justify-between mb-6">
        <div v-for="n in 5" :key="n" class="flex-1 text-center">
          <div class="font-semibold" :class="step >= n ? 'text-blue-600' : 'text-gray-400'">Step {{ n }}</div>
          <div class="text-sm mt-1" :class="step >= n ? 'text-blue-500' : 'text-gray-400'">{{ stepTitles[n-1] }}</div>
        </div>
      </div>

      <div class="flex-1 overflow-auto space-y-6">
        <div v-if="submitSuccess" class="rounded-xl bg-green-50 text-green-700 border border-green-200 px-4 py-3">
          {{ submitSuccess }}
        </div>
        <div v-if="submitError" class="rounded-xl bg-red-50 text-red-700 border border-red-200 px-4 py-3">
          {{ submitError }}
        </div>
        <!-- STEP 1 -->
        <div v-if="step === 1">
          <hr class="my-6 border-t border-gray-200" />
          <h2 class="text-xl font-bold mb-4">Maklumat Pemohon</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-2">Nama Pemohon</label>
              <input v-model="form.applicant_info.name"
                     class="input border border-gray-300 w-full px-3 py-2 rounded-xl"
                     placeholder="Nama Pemohon" />
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">No Kad Pengenalan</label>
              <input v-model="form.applicant_info.ic_no"
                     class="input border border-gray-300 w-full px-3 py-2 rounded-xl"
                     placeholder="No Kad Pengenalan" />
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">Tarikh Lahir</label>
              <input type="date"
                     v-model="form.applicant_info.birth_date"
                     class="input border border-gray-300 w-full px-3 py-2 rounded-xl" />
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">Tempat Lahir</label>
              <select v-model="form.applicant_info.birth_place"
                      class="input border border-gray-300 w-full px-3 py-2 rounded-xl">
                <option value="">-- Pilih tempat --</option>
                <option>Johor</option>
                <option>Kedah</option>
                <option>Kelantan</option>
                <option>Melaka</option>
                <option>Negeri Sembilan</option>
                <option>Pahang</option>
                <option>Perak</option>
                <option>Perlis</option>
                <option>Pulau Pinang</option>
                <option>Sabah</option>
                <option>Sarawak</option>
                <option>Selangor</option>
                <option>Terengganu</option>
                <option>Wilayah Persekutuan Kuala Lumpur</option>
                <option>Wilayah Persekutuan Labuan</option>
                <option>Wilayah Persekutuan Putrajaya</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">Jantina</label>
              <select v-model="form.applicant_info.gender"
                      class="input border border-gray-300 w-full px-3 py-2 rounded-xl">
                <option value="">-- Pilih jantina --</option>
                <option value="lelaki">Lelaki</option>
                <option value="perempuan">Perempuan</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">Warganegara</label>
              <select v-model="form.applicant_info.citizenship"
                      class="input border border-gray-300 w-full px-3 py-2 rounded-xl">
                <option value="">-- Pilih Warganegara --</option>
                <option value="Warganegara">Warganegara</option>
                <option value="Bukan Warganegara">Bukan Warganegara</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">Agama</label>
              <div class="flex flex-col space-y-2">
                <label class="flex items-center">
                  <input class="mr-2" type="radio" value="Islam" v-model="religionSelection" />
                  <span>Islam</span>
                </label>
                <label class="flex items-center">
                  <input class="mr-2" type="radio" value="Buddha" v-model="religionSelection" />
                  <span>Buddha</span>
                </label>
                <label class="flex items-center">
                  <input class="mr-2" type="radio" value="Hindu" v-model="religionSelection" />
                  <span>Hindu</span>
                </label>
                <label class="flex items-center">
                  <input class="mr-2" type="radio" value="Kristian" v-model="religionSelection" />
                  <span>Kristian</span>
                </label>
                <label class="flex items-center">
                  <input class="mr-2" type="radio" value="Lain-lain" v-model="religionSelection" />
                  <span>Lain-lain</span>
                </label>
                <div v-if="religionSelection === 'Lain-lain'" class="mt-2">
                  <input v-model="customReligion"
                         placeholder="Sila nyatakan"
                         class="input border border-gray-300 w-full px-3 py-2 rounded-xl" />
                </div>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">Bangsa</label>
              <div class="flex flex-col space-y-2">
                <label class="flex items-center">
                  <input class="mr-2" type="radio" value="Melayu" v-model="ethnicitySelection" />
                  <span>Melayu</span>
                </label>
                <label class="flex items-center">
                  <input class="mr-2" type="radio" value="Cina" v-model="ethnicitySelection" />
                  <span>Cina</span>
                </label>
                <label class="flex items-center">
                  <input class="mr-2" type="radio" value="India" v-model="ethnicitySelection" />
                  <span>India</span>
                </label>
                <label class="flex items-center">
                  <input class="mr-2" type="radio" value="Lain-lain" v-model="ethnicitySelection" />
                  <span>Lain-lain</span>
                </label>
                <div v-if="ethnicitySelection === 'Lain-lain'" class="mt-2">
                  <input v-model="customEthnicity"
                         placeholder="Sila nyatakan"
                         class="input border border-gray-300 w-full px-3 py-2 rounded-xl" />
                </div>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">Status Perkahwinan</label>
              <select v-model="form.applicant_info.marital_status"
                      class="input border border-gray-300 w-full px-3 py-2 rounded-xl">
                <option value="">-- Pilih Status --</option>
                <option value="Bujang">Bujang</option>
                <option value="Berkahwin">Berkahwin</option>
                <option value="Duda">Duda</option>
                <option value="Janda">Janda</option>
                <option value="Balu">Balu</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">Pekerjaan</label>
              <input v-model="form.applicant_info.occupation"
                     class="input border border-gray-300 w-full px-3 py-2 rounded-xl"
                     placeholder="Pekerjaan" />
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">Pendapatan (RM)</label>
              <input v-model="form.applicant_info.income"
                     class="input border border-gray-300 w-full px-3 py-2 rounded-xl"
                     placeholder="Pendapatan (contoh: 2,000)" />
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">Alamat Kediaman Pemohon</label>
              <input v-model="form.applicant_info.home_address"
                     class="input border border-gray-300 w-full px-3 py-2 rounded-xl"
                     placeholder="Alamat Kediaman Pemohon" />
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">Poskod</label>
              <input v-model="form.applicant_info.postcode"
                     class="input border border-gray-300 w-full px-3 py-2 rounded-xl"
                     placeholder="Poskod" />
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">Negeri</label>
              <input v-model="form.applicant_info.state"
                     class="input border border-gray-300 w-full px-3 py-2 rounded-xl"
                     placeholder="Negeri" />
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">Daerah</label>
              <input v-model="form.applicant_info.district"
                     class="input border border-gray-300 w-full px-3 py-2 rounded-xl"
                     placeholder="Daerah" />
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">No Telefon</label>
              <input v-model="form.applicant_info.phone_number"
                     class="input border border-gray-300 w-full px-3 py-2 rounded-xl"
                     placeholder="No Telefon" />
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">Emel</label>
              <input v-model="form.applicant_info.email"
                     class="input border border-gray-300 w-full px-3 py-2 rounded-xl"
                     placeholder="Emel" />
            </div>
          </div>
          <hr class="my-6 border-t border-gray-200" />
        </div>

        <!-- STEP 2 -->
        <div v-if="step === 2">
          <hr class="my-6 border-t border-gray-200" />
          <h2 class="text-xl font-bold mb-4">Maklumat Perniagaan / Syarikat</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-2">Nama Perniagaan / Syarikat</label>
              <input v-model="form.company_info.company_name"
                     class="input border border-gray-300 w-full px-3 py-2 rounded-xl"
                     placeholder="Nama Perniagaan / Syarikat" />
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">Alamat Perniagaan (Premis)</label>
              <input v-model="form.company_info.company_address"
                     class="input border border-gray-300 w-full px-3 py-2 rounded-xl"
                     placeholder="Alamat Perniagaan (Premis)" />
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">Poskod</label>
              <input v-model="form.company_info.company_postcode"
                     class="input border border-gray-300 w-full px-3 py-2 rounded-xl"
                     placeholder="Poskod" />
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">Negeri</label>
              <input v-model="form.company_info.company_state"
                     class="input border border-gray-300 w-full px-3 py-2 rounded-xl"
                     placeholder="Negeri" />
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">Daerah</label>
              <input v-model="form.company_info.company_district"
                     class="input border border-gray-300 w-full px-3 py-2 rounded-xl"
                     placeholder="Daerah" />
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">No Telefon</label>
              <input v-model="form.company_info.company_phone"
                     class="input border border-gray-300 w-full px-3 py-2 rounded-xl"
                     placeholder="No Telefon" />
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">No Pendaftaran Perniagaan / Syarikat</label>
              <input v-model="form.company_info.company_registration_number"
                     class="input border border-gray-300 w-full px-3 py-2 rounded-xl"
                     placeholder="No Pendaftaran Perniagaan / Syarikat" />
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">Tarikh Daftar</label>
              <input type="date"
                     v-model="form.company_info.company_registration_date"
                     class="input border border-gray-300 w-full px-3 py-2 rounded-xl" />
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">Tarikh Tamat</label>
              <input type="date"
                     v-model="form.company_info.company_registration_expiry_date"
                     class="input border border-gray-300 w-full px-3 py-2 rounded-xl" />
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium mb-2">Bilangan Pekerja</label>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center">
                  <label class="w-28 text-sm">Melayu</label>
                  <input type="number" min="0" v-model="form.company_info.employee_malay"
                         class="input border border-gray-300 w-full px-3 py-2 rounded-xl" />
                </div>

                <div class="flex items-center">
                  <label class="w-28 text-sm">Cina</label>
                  <input type="number" min="0" v-model="form.company_info.employee_chinese"
                         class="input border border-gray-300 w-full px-3 py-2 rounded-xl" />
                </div>

                <div class="flex items-center">
                  <label class="w-28 text-sm">India</label>
                  <input type="number" min="0" v-model="form.company_info.employee_indian"
                         class="input border border-gray-300 w-full px-3 py-2 rounded-xl" />
                </div>

                <div class="flex items-center">
                  <label class="w-28 text-sm">Lain-lain</label>
                  <input type="number" min="0" v-model="form.company_info.employee_others"
                         class="input border border-gray-300 w-full px-3 py-2 rounded-xl" />
                </div>
              </div>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium mb-2">Waktu Beroperasi</label>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs mb-1">Mula</label>
                  <input type="time"
                         v-model="form.company_info.company_operation_start"
                         class="input border border-gray-300 w-full px-3 py-2 rounded-xl" />
                </div>

                <div>
                  <label class="block text-xs mb-1">Tamat</label>
                  <input type="time"
                         v-model="form.company_info.company_operation_end"
                         class="input border border-gray-300 w-full px-3 py-2 rounded-xl" />
                </div>
              </div>
            </div>
          </div>

          <hr class="my-6 border-t border-gray-200" />

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-2">Alamat Perniagaan (Ibu Pejabat)</label>
              <input v-model="form.company_info.company_address_hq"
                      class="input border border-gray-300 w-full px-3 py-2 rounded-xl"
                      placeholder="Alamat Perniagaan (Ibu Pejabat)" />
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">Poskod</label>
              <input v-model="form.company_info.company_postcode_hq"
                      class="input border border-gray-300 w-full px-3 py-2 rounded-xl"
                      placeholder="Poskod" />
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">Negeri</label>
              <input v-model="form.company_info.company_state_hq"
                      class="input border border-gray-300 w-full px-3 py-2 rounded-xl"
                      placeholder="Negeri" />
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">Daerah</label>
              <input v-model="form.company_info.company_district_hq"
                      class="input border border-gray-300 w-full px-3 py-2 rounded-xl"
                      placeholder="Daerah" />
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">No Telefon</label>
              <input v-model="form.company_info.company_phone_hq"
                      class="input border border-gray-300 w-full px-3 py-2 rounded-xl"
                      placeholder="No Telefon" />
            </div>
          </div>

          <hr class="my-6 border-t border-gray-200" />

          <div class="overflow-auto">
            <div class="flex justify-between items-center mb-2">
              <h2 class="text-lg font-semibold">Jenis Lesen Yang Dipohon</h2>
              <button type="button" @click="addLicenseTypeRow" class="px-3 py-1 bg-green-600 text-white rounded-xl">Add Row</button>
            </div>
            <table class="w-full table-auto border-collapse">
              <thead>
                <tr class="bg-gray-100">
                  <th class="border px-2 py-1 text-center">No</th>
                  <th class="border px-2 py-1 text-left">Aktiviti Perniagaan Dijalankan</th>
                  <th class="border px-2 py-1 text-left">Keluasan (m³)</th>
                  <th class="border px-2 py-1 text-left">Unit / Bil Bilik</th>
                  <th class="border px-2 py-1 text-center">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(row, rIndex) in form.company_info.license_type" :key="rIndex">
                  <td class="border px-2 py-1 text-center">{{ rIndex + 1 }}</td>
                  <td class="border px-2 py-1">
                    <input v-model="row.aktiviti" class="w-full px-2 py-1 rounded-xl border border-gray-300" />
                  </td>
                  <td class="border px-2 py-1">
                    <input v-model="row.keluasan" class="w-full px-2 py-1 rounded-xl border border-gray-300" />
                  </td>
                  <td class="border px-2 py-1">
                    <input v-model="row.unit_bilik" class="w-full px-2 py-1 rounded-xl border border-gray-300" />
                  </td>
                  <td class="border px-2 py-1 text-center">
                    <button type="button" @click="removeLicenseTypeRow(rIndex)" class="px-2 py-1 bg-red-600 text-white rounded-xl" :disabled="form.company_info.license_type.length <= 1">Remove</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <hr class="my-6 border-t border-gray-200" />
        </div>

        <!-- STEP 3 -->
        <div v-if="step === 3">
          <hr class="my-6 border-t border-gray-200" />
          <h2 class="text-xl font-bold mb-4">Maklumat Iklan / Papan Tanda</h2>

          <div class="mt-4">
            <div class="flex justify-between items-center mb-2">
              <label class="block text-sm font-medium">Butiran Iklan / Papan Tanda</label>
              <button type="button" @click="addAdvertismentRow" class="px-3 py-1 bg-green-600 text-white rounded-xl">Add Row</button>
            </div>
            <div class="overflow-auto">
              <table class="w-full table-auto border-collapse">
                <thead>
                  <tr class="bg-gray-100">
                    <th class="border px-2 py-1 text-center">No</th>
                    <th class="border px-2 py-1 text-left">Jenis Iklan</th>
                    <th class="border px-2 py-1 text-left">Cara Pemasangan</th>
                    <th class="border px-2 py-1 text-left">Panjang (M)</th>
                    <th class="border px-2 py-1 text-left">Lebar (M)</th>
                    <th class="border px-2 py-1 text-left">Bil Iklan</th>
                    <th class="border px-2 py-1 text-center">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(row, rIndex) in form.advertisment_info.dynamic_table_rows" :key="rIndex">
                    <td class="border px-2 py-1 text-center">{{ rIndex + 1 }}</td>
                    <td class="border px-2 py-1">
                      <select v-model="row.type" class="w-full px-2 py-1 rounded-xl border border-gray-300">
                        <option value="">-- Pilih --</option>
                        <option v-for="opt in advertismentOptions1" :key="opt" :value="opt">{{ opt }}</option>
                      </select>
                    </td>
                    <td class="border px-2 py-1">
                      <select v-model="row.structure" class="w-full px-2 py-1 rounded-xl border border-gray-300">
                        <option value="">-- Pilih --</option>
                        <option v-for="opt in advertismentOptions2" :key="opt" :value="opt">{{ opt }}</option>
                      </select>
                    </td>
                    <td class="border px-2 py-1">
                      <input v-model="row.length" class="w-full px-2 py-1 rounded-xl border border-gray-300" />
                    </td>
                    <td class="border px-2 py-1">
                      <input v-model="row.width" class="w-full px-2 py-1 rounded-xl border border-gray-300" />
                    </td>
                    <td class="border px-2 py-1">
                      <input v-model="row.number_of_ads" class="w-full px-2 py-1 rounded-xl border border-gray-300" />
                    </td>
                    <td class="border px-2 py-1 text-center">
                      <button type="button" @click="removeAdvertismentRow(rIndex)" class="px-2 py-1 bg-red-600 text-white rounded-xl" :disabled="form.advertisment_info.dynamic_table_rows.length <= 1">Remove</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        <hr class="my-6 border-t border-gray-200" />
        </div>

        <!-- STEP 4 -->
        <div v-if="step === 4">
          <hr class="my-6 border-t border-gray-200" />
          <h2 class="text-xl font-bold mb-4">Dokumen Sokongan</h2>
          <div class="grid grid-cols-2 gap-6">
            <div class="space-y-4">
              <div class="flex flex-col">
                <label class="text-sm font-medium mb-1">Memorandum / Borang A / Borang B / Borang D</label>
                <div class="flex items-center gap-3">
                  <label class="inline-flex w-fit items-center px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded-xl cursor-pointer">
                    <input type="file" class="hidden" @change="(e) => handleDocumentChange(1, e)" />
                    <span>Choose File</span>
                  </label>
                  <span class="text-xs text-gray-600">{{ form.document1Name || 'No file chosen' }}</span>
                </div>
              </div>

              <div class="flex flex-col">
                <label class="text-sm font-medium mb-1">Pelan lokasi premis beserta gambar premis [2 salinan]</label>
                <div class="flex items-center gap-3">
                  <label class="inline-flex w-fit items-center px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded-xl cursor-pointer">
                    <input type="file" class="hidden" @change="(e) => handleDocumentChange(2, e)" />
                    <span>Choose File</span>
                  </label>
                  <span class="text-xs text-gray-600">{{ form.document2Name || 'No file chosen' }}</span>
                </div>
              </div>

              <div class="flex flex-col">
                <label class="text-sm font-medium mb-1">Pelan lantai premis / kawasan (ukuran dalam meter persegi) [2 salinan] </label>
                <div class="flex items-center gap-3">
                  <label class="inline-flex w-fit items-center px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded-xl cursor-pointer">
                    <input type="file" class="hidden" @change="(e) => handleDocumentChange(3, e)" />
                    <span>Choose File</span>
                  </label>
                  <span class="text-xs text-gray-600">{{ form.document3Name || 'No file chosen' }}</span>
                </div>
              </div>

              <div class="flex flex-col">
                <label class="text-sm font-medium mb-1">Surat perjanjian atau kebenaran tuan bangunan / tanah yang disetemkan (jika bangunan / tanah yang disewa)</label>
                <div class="flex items-center gap-3">
                  <label class="inline-flex w-fit items-center px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded-xl cursor-pointer">
                    <input type="file" class="hidden" @change="(e) => handleDocumentChange(4, e)" />
                    <span>Choose File</span>
                  </label>
                  <span class="text-xs text-gray-600">{{ form.document4Name || 'No file chosen' }}</span>
                </div>
              </div>

              <div class="flex flex-col">
                <label class="text-sm font-medium mb-1">Salinan geran tanah / Lesen Pendudukan Sementara (LPS) / lain-lain dokumen yang berkaitan</label>
                <div class="flex items-center gap-3">
                  <label class="inline-flex w-fit items-center px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded-xl cursor-pointer">
                    <input type="file" class="hidden" @change="(e) => handleDocumentChange(5, e)" />
                    <span>Choose File</span>
                  </label>
                  <span class="text-xs text-gray-600">{{ form.document5Name || 'No file chosen' }}</span>
                </div>
              </div>
            </div>

            <div class="space-y-4">
              <div class="flex flex-col">
                <label class="text-sm font-medium mb-1">Salinan Sijil Kelayakan Menduduki Bangunan / Sementara (CF/CCC) (TCF)</label>
                <div class="flex items-center gap-3">
                  <label class="inline-flex w-fit items-center px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded-xl cursor-pointer">
                    <input type="file" class="hidden" @change="(e) => handleDocumentChange(6, e)" />
                    <span>Choose File</span>
                  </label>
                  <span class="text-xs text-gray-600">{{ form.document6Name || 'No file chosen' }}</span>
                </div>
              </div>

              <div class="flex flex-col">
                <label class="text-sm font-medium mb-1">Gambar pemohon berukuran passport [2 keping]</label>
                <div class="flex items-center gap-3">
                  <label class="inline-flex w-fit items-center px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded-xl cursor-pointer">
                    <input type="file" class="hidden" @change="(e) => handleDocumentChange(7, e)" />
                    <span>Choose File</span>
                  </label>
                  <span class="text-xs text-gray-600">{{ form.document7Name || 'No file chosen' }}</span>
                </div>
              </div>

              <div class="flex flex-col">
                <label class="text-sm font-medium mb-1">Salinan Kad Pengenalan pemohon</label>
                <div class="flex items-center gap-3">
                  <label class="inline-flex w-fit items-center px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded-xl cursor-pointer">
                    <input type="file" class="hidden" @change="(e) => handleDocumentChange(8, e)" />
                    <span>Choose File</span>
                  </label>
                  <span class="text-xs text-gray-600">{{ form.document8Name || 'No file chosen' }}</span>
                </div>
              </div>

              <div class="flex flex-col">
                <label class="text-sm font-medium mb-1">Senarai nama semua Pengendali Makanan / Pembantu (Perniagaan Makanan) serta 2 salinan Kad Pengenalan dan 2 keping gambar berukuran passport bagi setiap orang (jika berkenaan)</label>
                <div class="flex items-center gap-3">
                  <label class="inline-flex w-fit items-center px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded-xl cursor-pointer">
                    <input type="file" class="hidden" @change="(e) => handleDocumentChange(9, e)" />
                    <span>Choose File</span>
                  </label>
                  <span class="text-xs text-gray-600">{{ form.document9Name || 'No file chosen' }}</span>
                </div>
              </div>

              <div class="flex flex-col">
                <label class="text-sm font-medium mb-1">Carta Proses Pengeluaran Pengilangan / Pemerosesan (jika ada)</label>
                <div class="flex items-center gap-3">
                  <label class="inline-flex w-fit items-center px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded-xl cursor-pointer">
                    <input type="file" class="hidden" @change="(e) => handleDocumentChange(10, e)" />
                    <span>Choose File</span>
                  </label>
                  <span class="text-xs text-gray-600">{{ form.document10Name || 'No file chosen' }}</span>
                </div>
              </div>
            </div>
          </div>
          <hr class="my-6 border-t border-gray-200" />
        </div>

        <!-- STEP 5 -->
        <div v-if="step === 5">
          <hr class="my-6 border-t border-gray-200" />
          <h2 class="text-xl font-bold mb-4">Pengesahan</h2>

          <label class="flex items-center gap-2">
            <input type="checkbox" v-model="form.declaration.agree" />
            Dengan ini saya mengaku bahawa semua maklumat yang tercatat di dalam borang ini dan Lampiran-lampiran yang disertakan adalah betul dan benar.<br> Saya berjanji akan mematuhi segala syarat-syarat lesen yang ditetapkan.
          </label>
          <hr class="my-6 border-t border-gray-200" />
        </div>
      </div>

      <!-- Navigation -->
      <div class="mt-6 flex items-center">
        <div>
          <button v-if="step > 1"
                  @click="prevStep"
                  class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-xl cursor-pointer">
            ← Kembali
          </button>
        </div>

        <div class="ml-auto">
          <button v-if="step < 5"
                  @click="nextStep"
                  class="px-4 py-2 bg-[#2563EB] hover:bg-[#154dc5] dark:bg-[#60A5FA] text-white rounded-xl cursor-pointer">
            Seterusnya →
          </button>

          <button v-if="step === 5"
                  @click="submitForm"
                  :disabled="!form.declaration.agree || isSubmitting"
                  class="px-4 py-2 bg-black text-white rounded-xl cursor-pointer">
            {{ isSubmitting ? 'Sedang dihantar...' : 'Hantar Permohonan' }}
          </button>
        </div>
      </div>

    </div>

  </AppLayout>
</template>
