<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type LicenseApplication = {
    id: number;
    name?: string;
    ic_no?: string;
    company_name?: string;
    status?: string;
    created_at?: string;
};

const props = defineProps<{
    applications: LicenseApplication[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Senarai Permohonan', href: '/admin/license-applications' },
];

const applications = computed(() => props.applications ?? []);

const dateFormatter = new Intl.DateTimeFormat('en-GB', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
});

function formatDate(value?: string) {
    if (!value) return '-';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '-';
    return dateFormatter.format(date);
}

function approve(applicationId: number) {
    router.post(`/admin/license-applications/${applicationId}/approve`);
}

function reject(applicationId: number) {
    router.post(`/admin/license-applications/${applicationId}/reject`);
}
</script>

<template>
    <Head title="Senarai Permohonan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="w-full h-full flex flex-col p-6 bg-white dark:bg-black rounded-xl shadow dark:shadow-black/30"
        >
            <div class="flex-1 overflow-auto space-y-6">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <h2
                            class="text-xl font-bold text-slate-900 dark:text-slate-100"
                        >
                            Senarai Permohonan
                        </h2>
                        <p class="text-sm text-slate-600 dark:text-slate-400">
                            Senarai semua permohonan lesen penginapan.
                        </p>
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/60 p-4 overflow-x-auto"
                >
                    <table class="w-full table-auto border-collapse">
                        <thead>
                            <tr class="bg-slate-100 dark:bg-slate-800">
                                <th
                                    class="border border-slate-200 dark:border-slate-700 px-3 py-2 text-left text-sm font-semibold"
                                >
                                    No
                                </th>
                                <th
                                    class="border border-slate-200 dark:border-slate-700 px-3 py-2 text-left text-sm font-semibold"
                                >
                                    Nama Pemohon
                                </th>
                                <th
                                    class="border border-slate-200 dark:border-slate-700 px-3 py-2 text-left text-sm font-semibold"
                                >
                                    No Kad Pengenalan
                                </th>
                                <th
                                    class="border border-slate-200 dark:border-slate-700 px-3 py-2 text-left text-sm font-semibold"
                                >
                                    Nama Syarikat
                                </th>
                                <th
                                    class="border border-slate-200 dark:border-slate-700 px-3 py-2 text-left text-sm font-semibold"
                                >
                                    Tarikh Mohon
                                </th>
                                <th
                                    class="border border-slate-200 dark:border-slate-700 px-3 py-2 text-left text-sm font-semibold"
                                >
                                    Status
                                </th>
                                <th
                                    class="border border-slate-200 dark:border-slate-700 px-3 py-2 text-left text-sm font-semibold"
                                >
                                    Tindakan
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(application, index) in applications"
                                :key="application.id"
                            >
                                <td
                                    class="border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm text-slate-900 dark:text-slate-100"
                                >
                                    {{ index + 1 }}
                                </td>
                                <td
                                    class="border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm text-slate-900 dark:text-slate-100"
                                >
                                    {{ application.name || '-' }}
                                </td>
                                <td
                                    class="border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm text-slate-900 dark:text-slate-100"
                                >
                                    {{ application.ic_no || '-' }}
                                </td>
                                <td
                                    class="border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm text-slate-900 dark:text-slate-100"
                                >
                                    {{ application.company_name || '-' }}
                                </td>
                                <td
                                    class="border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm text-slate-900 dark:text-slate-100"
                                >
                                    {{ formatDate(application.created_at) }}
                                </td>
                                <td
                                    class="border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm"
                                >
                                    <span
                                        class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                                        :class="application.status === 'Diluluskan'
                                            ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'
                                            : application.status === 'Ditolak'
                                                ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'
                                                : 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-100'"
                                    >
                                        {{ application.status || 'Dalam Proses' }}
                                    </span>
                                </td>
                                <td
                                    class="border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm"
                                >
                                    <div class="flex flex-wrap gap-2">
                                        <button
                                            type="button"
                                            class="px-3 py-1 rounded-lg bg-green-600 text-white text-xs font-semibold hover:bg-green-700"
                                            :disabled="application.status === 'Diluluskan'"
                                            @click="approve(application.id)"
                                        >
                                            Lulus
                                        </button>
                                        <button
                                            type="button"
                                            class="px-3 py-1 rounded-lg bg-red-600 text-white text-xs font-semibold hover:bg-red-700"
                                            :disabled="application.status === 'Ditolak'"
                                            @click="reject(application.id)"
                                        >
                                            Tolak
                                        </button>
                                        <Link
                                            class="px-3 py-1 rounded-lg bg-slate-700 text-white text-xs font-semibold hover:bg-slate-800"
                                            :href="`/admin/license-applications/${application.id}`"
                                        >
                                            Lihat
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!applications.length">
                                <td
                                    colspan="7"
                                    class="border border-slate-200 dark:border-slate-700 px-3 py-6 text-center text-sm text-slate-600 dark:text-slate-400"
                                >
                                    Tiada permohonan ditemui.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
