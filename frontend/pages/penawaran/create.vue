<template>
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
</template>

<script setup lang="ts">
import type { PenawaranMetaResponse, PenawaranSubmitPayload } from '~/types/api'

definePageMeta({
  middleware: 'auth',
})

const apiFetch = useApi()

const fallbackMeta: PenawaranMetaResponse = {
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

const { data: metaData } = await useAsyncData('penawaran-meta-create', () => apiFetch<PenawaranMetaResponse>('/api/penawarans/meta'))

const meta = computed(() => metaData.value ?? fallbackMeta)
const saving = ref(false)
const errorMessage = ref('')

async function submit(payload: PenawaranSubmitPayload) {
  saving.value = true
  errorMessage.value = ''

  try {
    const result = await apiFetch<{ data: { id: number } }>('/api/penawarans', {
      method: 'POST',
      body: payload,
    })

    await navigateTo(`/penawaran/${result.data.id}`)
  } catch (error: any) {
    errorMessage.value = extractApiMessage(error, 'Gagal menyimpan penawaran.')
  } finally {
    saving.value = false
  }
}

function extractApiMessage(error: any, fallback: string): string {
  if (error?.data?.message) {
    return error.data.message
  }

  const errors = error?.data?.errors
  if (errors && typeof errors === 'object') {
    const firstError = Object.values(errors).flat()[0]
    if (typeof firstError === 'string') {
      return firstError
    }
  }

  return fallback
}
</script>
