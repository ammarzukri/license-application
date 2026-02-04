<script setup lang="ts">
import { computed } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import type { AppPageProps, BreadcrumbItem } from '@/types'

type LicenseApplication = {
	name?: string
	ic_no?: string
	company_name?: string
	status?: string
	payment_status?: string
	payment_amount?: number
	created_at?: string
	updated_at?: string
}

type LicenseType = {
	aktiviti?: string
}

const props = defineProps<{
	application?: LicenseApplication | null
	licenseTypes?: LicenseType[]
}>()

const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Dashboard', href: '/dashboard' },
	{ title: 'Status Permohonan', href: '#' },
]

const activityList = computed(() =>
	(props.licenseTypes ?? [])
		.map((item) => item.aktiviti)
		.filter((value): value is string => Boolean(value && value.trim()))
)

const statusLabel = computed(() => props.application?.status || 'Dalam Proses')
const paymentStatus = computed(() => props.application?.payment_status || 'Belum Dibayar')
const page = usePage<
	AppPageProps<{
		flash?: {
			payment?: {
				status?: string
				message?: string
			}
		}
	}>
>()
const paymentFlash = computed(() => page.props.flash?.payment)
</script>

<template>
	<Head title="Status Permohonan" />

	<AppLayout :breadcrumbs="breadcrumbs">
		<div class="w-full h-full flex flex-col p-6 bg-white dark:bg-black rounded-xl shadow dark:shadow-black/30">
			<div class="flex-1 overflow-auto space-y-6">
				<div class="rounded-xl bg-blue-50 text-blue-700 border border-blue-200 px-4 py-3 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-900">
					Permohonan anda telah direkodkan. Anda hanya boleh memohon sekali.
				</div>

				<div
					v-if="paymentFlash?.message"
					:class="[
						'rounded-xl px-4 py-3 border text-sm',
						paymentFlash?.status === 'success'
							? 'bg-green-50 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-300 dark:border-green-900'
							: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-900',
					]"
				>
					{{ paymentFlash.message }}
				</div>

				<div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/60 p-6">
					<div class="flex items-center justify-between flex-wrap gap-4 mb-6">
						<div>
							<h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">Status Permohonan</h2>
							<p class="text-sm text-slate-600 dark:text-slate-400">Ringkasan maklumat permohonan anda</p>
						</div>
						<div
							:class="[
								'px-3 py-1 rounded-full text-sm font-semibold',
								statusLabel === 'Diluluskan'
									? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'
									: statusLabel === 'Ditolak'
									? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'
									: 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-100',
							]"
						>
							{{ statusLabel }}
						</div>
					</div>

					<div v-if="application" class="space-y-8">
						<section>
							<h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Maklumat Pemohon</h3>
							<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
								<div>
									<div class="text-xs font-semibold text-slate-600 dark:text-slate-400">Nama Pemohon</div>
									<div class="text-sm text-slate-900 dark:text-slate-100">{{ application.name || '-' }}</div>
								</div>
								<div>
									<div class="text-xs font-semibold text-slate-600 dark:text-slate-400">No Kad Pengenalan</div>
									<div class="text-sm text-slate-900 dark:text-slate-100">{{ application.ic_no || '-' }}</div>
								</div>
								<div>
									<div class="text-xs font-semibold text-slate-600 dark:text-slate-400">Nama Perniagaan / Syarikat</div>
									<div class="text-sm text-slate-900 dark:text-slate-100">{{ application.company_name || '-' }}</div>
								</div>
								<div>
									<div class="text-xs font-semibold text-slate-600 dark:text-slate-400">Status Permohonan</div>
									<div class="text-sm text-slate-900 dark:text-slate-100">{{ statusLabel }}</div>
								</div>
								<div>
									<div class="text-xs font-semibold text-slate-600 dark:text-slate-400">Status Pembayaran</div>
									<div class="text-sm text-slate-900 dark:text-slate-100">{{ paymentStatus }}</div>
								</div>
							</div>
						</section>

						<section>
							<h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Lesen Yang Dipohon</h3>
							<div class="space-y-3">
								<div v-if="activityList.length" class="space-y-2">
									<div v-for="(activity, idx) in activityList" :key="`activity-${idx}`" class="rounded-xl border border-slate-200 dark:border-slate-700 p-3">
										<div class="text-xs font-semibold text-slate-600 dark:text-slate-400">Lesen #{{ idx + 1 }}</div>
										<div class="text-sm text-slate-900 dark:text-slate-100">{{ activity }}</div>
									</div>
								</div>
								<div v-else class="text-sm text-slate-600 dark:text-slate-400">Tiada lesen direkodkan.</div>
							</div>
						</section>

						<div v-if="statusLabel === 'Diluluskan'" class="pt-2">
							<div
								v-if="paymentStatus === 'Berjaya'"
								class="rounded-xl bg-green-50 text-green-700 border border-green-200 px-4 py-3 text-sm dark:bg-green-900/30 dark:text-green-300 dark:border-green-900"
							>
								Pembayaran berjaya diterima.
							</div>
							<div
								v-else
								class="flex flex-col gap-3"
							>
								<div
									v-if="paymentStatus === 'Gagal'"
									class="rounded-xl bg-red-50 text-red-700 border border-red-200 px-4 py-3 text-sm dark:bg-red-900/30 dark:text-red-300 dark:border-red-900"
								>
									Pembayaran gagal. Sila cuba lagi.
								</div>
								<Link
									href="/license/payment/start"
									class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700"
								>
									Teruskan ke Pembayaran
								</Link>
								<p class="text-xs text-slate-500 dark:text-slate-400">Jumlah bayaran: RM100</p>
							</div>
						</div>
					</div>

					<div v-else class="text-sm text-slate-600 dark:text-slate-400">
						Tiada rekod permohonan ditemui.
					</div>
				</div>
			</div>
		</div>
	</AppLayout>
</template>
