<template>
  <DefaultLayout>
    <div class="space-y-6">
      <PenawaranForm
        mode="edit"
        :meta="meta"
        :customers="meta.customers"
        :mitras="meta.mitras"
        :initial-values="penawaran"
        :busy="saving"
        :error-message="errorMessage"
        submit-label="Simpan Perubahan"
        cancel-to="/penawaran"
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

const props = defineProps({
  penawaranId: {
    type: [Number, String],
    required: true,
  },
})

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
const penawaran = ref(null)
const saving = ref(false)
const errorMessage = ref('')

const [metaResponse, detailResponse] = await Promise.all([
  api.get('/api/penawarans/meta'),
  api.get(`/api/penawarans/${props.penawaranId}`),
])

meta.value = metaResponse.data ?? fallbackMeta
penawaran.value = detailResponse.data?.data ?? null

async function submit(payload) {
  saving.value = true
  errorMessage.value = ''

  try {
    const result = await api.put(`/api/penawarans/${props.penawaranId}`, payload)
    const id = result.data?.data?.id ?? props.penawaranId
    window.location.href = `/penawaran/${id}`
  } catch (error) {
    errorMessage.value = extractApiMessage(error, 'Gagal memperbarui penawaran.')
  } finally {
    saving.value = false
  }
}
</script>
