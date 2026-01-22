<script setup lang="ts">
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { dashboard } from '@/routes'
import { apply as licenseApply } from '@/routes/license'
import type { BreadcrumbItem } from '@/types'

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: dashboard().url },
  { title: 'Permohonan Lesen', href: licenseApply().url },
]

const step = ref(1)

const form = ref({
  business_info: {
    name: '',
    type: '',
  },
  owner_info: {
    name: '',
    ic: '',
  },
  premises_info: {
    address: '',
  },
  declaration: {
    agree: false,
  },
})

function nextStep() {
  // Step 1 validation
  if (step.value === 1) {
    if (!form.value.business_info.name || !form.value.business_info.type) {
      alert('Sila lengkapkan maklumat perniagaan')
      return
    }
  }

  // Step 2 validation
  if (step.value === 2) {
    if (!form.value.owner_info.name || !form.value.owner_info.ic) {
      alert('Sila lengkapkan maklumat pemilik')
      return
    }
  }

  // Step 3 validation
  if (step.value === 3) {
    if (!form.value.premises_info.address) {
      alert('Alamat premis diperlukan')
      return
    }
  }

  step.value++
}

function prevStep() {
  if (step.value > 1) step.value--
}

function submitForm() {
  console.log(form.value)
  // later → send to backend
}
</script>

<template>
  <Head title="Permohonan Lesen" />

  <AppLayout :breadcrumbs="breadcrumbs">

    <div class="w-full h-full flex flex-col p-6 bg-white rounded-xl shadow">

      <!-- Step Indicator -->
      <div class="flex justify-between mb-6">
        <div v-for="n in 4" :key="n"
             class="flex-1 text-center font-semibold"
             :class="step >= n ? 'text-blue-600' : 'text-gray-400'">
          Step {{ n }}
        </div>
      </div>

      <div class="flex-1 overflow-auto space-y-6">
        <!-- STEP 1 -->
        <div v-if="step === 1">
          <h2 class="text-xl font-bold mb-4">Maklumat Perniagaan</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-2">Nama Perniagaan</label>
              <input v-model="form.business_info.name"
                     class="input border border-gray-300 rounded w-full px-3 py-2 rounded-xl"
                     placeholder="Nama Perniagaan" />
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">Jenis Perniagaan</label>
              <input v-model="form.business_info.type"
                     class="input border border-gray-300 rounded w-full px-3 py-2 rounded-xl"
                     placeholder="Jenis Perniagaan" />
            </div>
          </div>
        </div>

        <!-- STEP 2 -->
        <div v-if="step === 2">
          <h2 class="text-xl font-bold mb-4">Maklumat Pemilik</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-2">Nama Pemilik</label>
              <input v-model="form.owner_info.name"
                     class="input border border-gray-300 rounded w-full px-3 py-2 rounded-xl"
                     placeholder="Nama Pemilik" />
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">No IC</label>
              <input v-model="form.owner_info.ic"
                     class="input border border-gray-300 rounded w-full px-3 py-2 rounded-xl"
                     placeholder="No IC" />
            </div>
          </div>
        </div>

        <!-- STEP 3 -->
        <div v-if="step === 3">
          <h2 class="text-xl font-bold mb-4">Maklumat Premis</h2>

          <div>
            <label class="block text-sm font-medium mb-2">Alamat Premis</label>
            <textarea v-model="form.premises_info.address"
                      class="input border border-gray-300 rounded w-full px-3 py-2 rounded-xl"
                      placeholder="Alamat Premis"></textarea>
          </div>
        </div>

        <!-- STEP 4 -->
        <div v-if="step === 4">
          <h2 class="text-xl font-bold mb-4">Pengesahan</h2>

          <label class="flex items-center gap-2">
            <input type="checkbox" v-model="form.declaration.agree" />
            Saya mengesahkan maklumat adalah benar
          </label>
        </div>
      </div>

      <!-- Navigation -->
      <div class="mt-6 flex items-center">
        <div>
          <button v-if="step > 1"
                  @click="prevStep"
                  class="px-4 py-2 bg-gray-200 rounded">
            ← Kembali
          </button>
        </div>

        <div class="ml-auto">
          <button v-if="step < 4"
                  @click="nextStep"
                  class="px-4 py-2 bg-[#2563EB] dark:bg-[#60A5FA] text-white rounded">
            Seterusnya →
          </button>

          <button v-if="step === 4"
                  @click="submitForm"
                  :disabled="!form.declaration.agree"
                  class="px-4 py-2 bg-black text-white rounded">
            Hantar Permohonan
          </button>
        </div>
      </div>

    </div>

  </AppLayout>
</template>
