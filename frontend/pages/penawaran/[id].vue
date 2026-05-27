<template>
  <div class="space-y-6">
    <section v-if="loadError" class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
      {{ loadError }}
    </section>

    <PenawaranForm
      v-if="detail"
      mode="edit"
      :meta="meta"
      :customers="meta.customers"
      :mitras="meta.mitras"
      :initial-values="detail"
      :busy="saving"
      :error-message="errorMessage"
      submit-label="Simpan Perubahan"
      @submit="submit"
    />

    <section v-if="actionMessage || actionError" class="panel p-6">
      <div
        v-if="actionMessage"
        class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
      >
        {{ actionMessage }}
      </div>
      <div
        v-if="actionError"
        class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
      >
        {{ actionError }}
      </div>
    </section>

    <section v-if="detail" class="flex flex-col gap-3 sm:flex-row sm:justify-between">
      <NuxtLink to="/penawaran" class="button-ghost justify-center">
        Kembali ke daftar
      </NuxtLink>
      <div class="flex flex-col gap-3 sm:flex-row">
        <button type="button" class="button-ghost justify-center" :disabled="busy" @click="sendEmail">
          {{ busy ? 'Memproses...' : 'Kirim Email' }}
        </button>
        <a :href="pdfLink" target="_blank" class="button-primary justify-center">
          Buka PDF
        </a>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import type { PenawaranDetail, PenawaranMetaResponse, PenawaranSubmitPayload } from '~/types/api'

definePageMeta({
  middleware: 'auth',
})

const route = useRoute()
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

const id = computed(() => String(route.params.id))
const backendBase = useRuntimeConfig().public.apiBase

const { data: metaData } = await useAsyncData('penawaran-meta-edit', () => apiFetch<PenawaranMetaResponse>('/api/penawarans/meta'))
const { data: detailData, error: detailError } = await useAsyncData(`penawaran-${id.value}`, () =>
  apiFetch<{ data: PenawaranDetail }>(`/api/penawarans/${id.value}`),
)

const meta = computed(() => metaData.value ?? fallbackMeta)
const detail = computed(() => detailData.value?.data ?? null)
const saving = ref(false)
const busy = ref(false)
const actionMessage = ref('')
const actionError = ref('')
const errorMessage = ref('')
const pdfLink = computed(() => `${backendBase}/penawaran/${id.value}/pdf?download=1`)
const loadError = computed(() => {
  if (!detailError.value) {
    return ''
  }

  return extractApiMessage(detailError.value, 'Gagal memuat data penawaran.')
})

async function submit(payload: PenawaranSubmitPayload) {
  saving.value = true
  errorMessage.value = ''

  try {
    const result = await apiFetch<{ data: { id: number } }>(`/api/penawarans/${id.value}`, {
      method: 'PUT',
      body: payload,
    })

    await navigateTo(`/penawaran/${result.data.id}`)
  } catch (error: any) {
    errorMessage.value = extractApiMessage(error, 'Gagal memperbarui penawaran.')
  } finally {
    saving.value = false
  }
}

async function sendEmail() {
  if (!detail.value || busy.value) return

  busy.value = true
  actionMessage.value = ''
  actionError.value = ''

  try {
    const response = await apiFetch<{ message?: string }>(`/api/penawarans/${detail.value.id}/send`, {
      method: 'POST',
    })

    actionMessage.value = response.message ?? 'Surat penawaran berhasil dikirim.'
  } catch (error: any) {
    actionError.value = extractApiMessage(error, 'Gagal mengirim penawaran.')
  } finally {
    busy.value = false
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
