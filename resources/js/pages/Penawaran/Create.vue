<template>
  <DefaultLayout>
    <div class="space-y-6">
      <PenawaranForm
        mode="create"
        :meta="meta"
        :customers="meta.customers"
        :mitras="meta.mitras"
        :busy="saving"
        :error-message="errorMessage"
        @submit="submit"
      />
    </div>
  </DefaultLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import DefaultLayout from '../../layouts/DefaultLayout.vue'
import PenawaranForm from '../../components/penawaran/PenawaranForm.vue'
import api, { extractApiMessage } from '../../utils/api'

const fallbackMeta = {
  nomor_preview: '-',
  to_company_options: [],
  customers: [],
  mitras: [],
  defaults: {
    tanggal: new Date().toISOString().slice(0, 10),
    tax_percent: 11,
    status: 'draft',
    jenis_kontrak: 'satuan',
    signature_role: 'Direktur',
    keterangan: '',
  },
  options: {
    jenis_kontrak: ['kontrak', 'satuan'],
    signature_role: ['Direktur', 'Manager', 'Sales'],
    satuan: ['month', 'pcs', 'item', 'unit'],
    status: ['draft', 'submitted', 'approved', 'rejected'],
  },
}

const meta = ref(fallbackMeta)
const saving = ref(false)
const errorMessage = ref('')

const response = await api.get('/api/penawarans/meta')
meta.value = response.data ?? fallbackMeta

async function submit(payload) {
  saving.value = true
  errorMessage.value = ''

  try {
    const result = await api.post('/api/penawarans', payload)
    const id = result.data?.data?.id
    if (id) {
      window.location.href = `/penawaran/${id}`
    } else {
      window.location.href = '/penawaran'
    }
  } catch (error) {
    errorMessage.value = extractApiMessage(error, 'Gagal menyimpan penawaran.')
  } finally {
    saving.value = false
  }
}
</script>
