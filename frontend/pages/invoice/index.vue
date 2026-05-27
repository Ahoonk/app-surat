<template>
  <div class="space-y-6">
    <section class="panel p-6">
      <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
          <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Modul</p>
          <h2 class="section-title mt-2">Invoice</h2>
          <p class="mt-3 text-sm text-slate-600">Daftar invoice untuk memantau pembayaran, tanggal cetak, dan dokumen turunan.</p>
        </div>
        <div class="text-sm text-slate-500">
          {{ rows.length }} invoice
        </div>
      </div>
    </section>

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

    <section class="panel overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full min-w-[1200px] border-collapse text-sm">
          <thead class="bg-slate-50 text-left text-slate-500">
            <tr>
              <th class="px-5 py-4 font-medium">Nomor</th>
              <th class="px-5 py-4 font-medium">Tanggal</th>
              <th class="px-5 py-4 font-medium">Customer</th>
              <th class="px-5 py-4 font-medium">Pembayaran</th>
              <th class="px-5 py-4 font-medium">Total</th>
              <th class="px-5 py-4 font-medium">Faktur Pajak</th>
              <th class="px-5 py-4 font-medium">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in rows" :key="item.id" class="border-t border-slate-200">
              <td class="px-5 py-4 font-medium text-slate-900">{{ item.nomor }}</td>
              <td class="px-5 py-4 text-slate-600">{{ formatDate(item.tanggal) }}</td>
              <td class="px-5 py-4 text-slate-600">{{ item.penawaran?.to_company || item.penawaran?.customer_nama || '-' }}</td>
              <td class="px-5 py-4 text-slate-600">
                <span :class="item.payment_status === 'paid' ? 'text-emerald-600' : 'text-amber-600'">
                  {{ paymentLabel(item.payment_status) }}
                </span>
                <div v-if="item.payment_date" class="text-xs text-slate-500">
                  {{ formatDate(item.payment_date) }}
                </div>
              </td>
              <td class="px-5 py-4 text-slate-600">{{ formatCurrency(item.total) }}</td>
              <td class="px-5 py-4 text-slate-600">
                <span v-if="item.faktur_pajak">{{ item.faktur_pajak.dokumen_name }}</span>
                <span v-else>-</span>
              </td>
              <td class="px-5 py-4">
                <div class="flex flex-wrap gap-2">
                  <NuxtLink :to="`/invoice/${item.id}`" class="button-ghost px-3 py-2 text-sm">
                    Detail
                  </NuxtLink>
                  <a :href="pdfLink(item.id)" target="_blank" class="button-ghost px-3 py-2 text-sm">
                    PDF
                  </a>
                  <button
                    type="button"
                    class="button-ghost px-3 py-2 text-sm"
                    :disabled="sendingInvoiceId === item.id"
                    @click="sendEmail(item)"
                  >
                    {{ sendingInvoiceId === item.id ? 'Memproses...' : 'Kirim Email' }}
                  </button>
                  <button type="button" class="button-ghost px-3 py-2 text-sm" @click="openPrintModal(item)">
                    Tanggal
                  </button>
                  <button
                    v-if="canVerify"
                    type="button"
                    class="button-primary px-3 py-2 text-sm"
                    :disabled="busyActionId === item.id"
                    @click="openVerifyModal(item)"
                  >
                    {{ busyActionId === item.id ? 'Memproses...' : 'Verifikasi' }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <div v-if="printModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="closePrintModal">
      <div class="w-full max-w-md rounded-[24px] bg-white p-6 shadow-2xl">
        <h3 class="text-lg font-semibold text-slate-900">Ubah Tanggal Cetak</h3>
        <p class="mt-2 text-sm text-slate-500">Tanggal ini akan ikut memperbarui nomor invoice dan surat jalan jika dibutuhkan.</p>

        <form class="mt-5 space-y-4" @submit.prevent="submitPrintDate">
          <label class="block">
            <span class="mb-2 block text-sm font-medium text-slate-700">Tanggal Cetak</span>
            <input v-model="printDateForm.tanggal" type="date" class="input" required />
          </label>

          <div v-if="actionError" class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ actionError }}
          </div>

          <div class="flex justify-end gap-3">
            <button type="button" class="button-ghost" @click="closePrintModal">Batal</button>
            <button type="submit" class="button-primary" :disabled="busyActionId !== null">
              {{ busyActionId !== null ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="verifyModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="closeVerifyModal">
      <div class="w-full max-w-md rounded-[24px] bg-white p-6 shadow-2xl">
        <h3 class="text-lg font-semibold text-slate-900">Verifikasi Pembayaran</h3>
        <p class="mt-2 text-sm text-slate-500">Tandai invoice sebagai sudah dibayarkan dengan tanggal pembayaran yang sesuai.</p>

        <form class="mt-5 space-y-4" @submit.prevent="submitVerifyPayment">
          <label class="block">
            <span class="mb-2 block text-sm font-medium text-slate-700">Tanggal Pembayaran</span>
            <input v-model="paymentForm.payment_date" type="date" class="input" required />
          </label>

          <div v-if="actionError" class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ actionError }}
          </div>

          <div class="flex justify-end gap-3">
            <button type="button" class="button-ghost" @click="closeVerifyModal">Batal</button>
            <button type="submit" class="button-primary" :disabled="busyActionId !== null">
              {{ busyActionId !== null ? 'Menyimpan...' : 'Verifikasi' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { formatCurrency, formatDate } from '~/utils/format'
import type { ApiListResponse, InvoicePaymentPayload, InvoiceSummary } from '~/types/api'

definePageMeta({
  middleware: 'auth',
})

const apiFetch = useApi()
const { session } = useSession()
const canVerify = computed(() => session.value?.user?.role === 'superadmin')
const backendBase = useRuntimeConfig().public.apiBase

const { data, refresh } = await useAsyncData('invoices', () => apiFetch<ApiListResponse<InvoiceSummary>>('/api/invoices'))
const rows = computed(() => data.value?.data ?? [])

const printModalOpen = ref(false)
const verifyModalOpen = ref(false)
const actionError = ref('')
const actionMessage = ref('')
const busyActionId = ref<number | null>(null)
const sendingInvoiceId = ref<number | null>(null)
const selectedInvoice = ref<InvoiceSummary | null>(null)

const printDateForm = reactive({
  tanggal: new Date().toISOString().slice(0, 10),
})

const paymentForm = reactive<InvoicePaymentPayload>({
  payment_date: new Date().toISOString().slice(0, 10),
})

function pdfLink(id: number): string {
  return `${backendBase}/invoice/${id}/pdf?download=1`
}

function paymentLabel(status: string | null | undefined): string {
  if (status === 'paid') {
    return 'Sudah Dibayarkan'
  }

  return 'Belum Dibayarkan'
}

function openPrintModal(item: InvoiceSummary) {
  selectedInvoice.value = item
  actionError.value = ''
  printDateForm.tanggal = item.tanggal
  printModalOpen.value = true
}

function openVerifyModal(item: InvoiceSummary) {
  selectedInvoice.value = item
  actionError.value = ''
  paymentForm.payment_date = new Date().toISOString().slice(0, 10)
  verifyModalOpen.value = true
}

function closePrintModal() {
  printModalOpen.value = false
  selectedInvoice.value = null
}

function closeVerifyModal() {
  verifyModalOpen.value = false
  selectedInvoice.value = null
}

async function sendEmail(item: InvoiceSummary) {
  if (sendingInvoiceId.value !== null) return

  sendingInvoiceId.value = item.id
  actionMessage.value = ''
  actionError.value = ''

  try {
    const response = await apiFetch<{ message?: string }>(`/api/invoices/${item.id}/send`, {
      method: 'POST',
    })

    actionMessage.value = response.message ?? 'Invoice berhasil dikirim.'
  } catch (error: any) {
    actionError.value = extractApiMessage(error, 'Gagal mengirim invoice.')
  } finally {
    sendingInvoiceId.value = null
  }
}

async function submitPrintDate() {
  if (!selectedInvoice.value) return

  busyActionId.value = selectedInvoice.value.id
  actionError.value = ''

  try {
    await apiFetch(`/api/invoices/${selectedInvoice.value.id}/update-print-date`, {
      method: 'POST',
      body: printDateForm,
    })

    await refresh()
    closePrintModal()
  } catch (error: any) {
    actionError.value = extractApiMessage(error, 'Gagal memperbarui tanggal cetak invoice.')
  } finally {
    busyActionId.value = null
  }
}

async function submitVerifyPayment() {
  if (!selectedInvoice.value) return

  busyActionId.value = selectedInvoice.value.id
  actionError.value = ''

  try {
    await apiFetch(`/api/invoices/${selectedInvoice.value.id}/verify-payment`, {
      method: 'POST',
      body: paymentForm,
    })

    await refresh()
    closeVerifyModal()
  } catch (error: any) {
    actionError.value = extractApiMessage(error, 'Gagal memverifikasi pembayaran.')
  } finally {
    busyActionId.value = null
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
