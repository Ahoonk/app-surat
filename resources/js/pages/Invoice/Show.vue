<template>
  <DefaultLayout>
    <div class="space-y-6">
      <section v-if="detail" class="panel overflow-hidden">
        <div class="grid gap-6 p-6 lg:grid-cols-[1.1fr_0.9fr]">
          <div>
            <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Detail Invoice</p>
            <h2 class="section-title mt-3">{{ detail.nomor }}</h2>
            <p class="mt-2 text-sm text-slate-600">
              {{ detail.penawaran?.to_company || detail.penawaran?.customer_nama || '-' }} -
              {{ formatDate(detail.tanggal) }}
            </p>
          </div>

          <div class="rounded-[22px] bg-gradient-to-br from-slate-950 to-cyan-900 p-5 text-white">
            <p class="text-xs uppercase tracking-[0.3em] text-cyan-200/70">Total</p>
            <h3 class="mt-3 text-3xl font-semibold">{{ formatCurrency(detail.total) }}</h3>
            <p class="mt-2 text-sm text-slate-200/80">
              {{ paymentLabel(detail.payment_status) }}
              <span v-if="detail.payment_date"> - {{ formatDate(detail.payment_date) }}</span>
            </p>
          </div>
        </div>
      </section>

      <section v-if="detail" class="grid gap-6 lg:grid-cols-[1fr_0.9fr]">
        <article class="panel p-6">
          <h3 class="text-lg font-semibold text-slate-900">Ringkasan Penawaran</h3>
          <div class="mt-4 space-y-3 text-sm">
            <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
              <span class="text-slate-500">Nomor Penawaran</span>
              <span class="font-medium text-slate-900">{{ detail.penawaran?.nomor ?? '-' }}</span>
            </div>
            <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
              <span class="text-slate-500">Status Penawaran</span>
              <span class="font-medium text-slate-900">{{ detail.penawaran?.status ?? '-' }}</span>
            </div>
            <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
              <span class="text-slate-500">Subtotal</span>
              <span class="font-medium text-slate-900">{{ formatCurrency(detail.penawaran?.subtotal ?? 0) }}</span>
            </div>
            <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
              <span class="text-slate-500">Pajak</span>
              <span class="font-medium text-slate-900">{{ formatCurrency(detail.penawaran?.tax_amount ?? 0) }}</span>
            </div>
            <div class="flex items-center justify-between rounded-2xl bg-cyan-50 px-4 py-3">
              <span class="font-medium text-cyan-800">Total</span>
              <span class="font-semibold text-cyan-950">{{ formatCurrency(detail.penawaran?.total ?? 0) }}</span>
            </div>
          </div>
        </article>

        <article class="panel p-6">
          <h3 class="text-lg font-semibold text-slate-900">Dokumen Turunan</h3>
          <div class="mt-4 space-y-3 text-sm">
            <div class="rounded-2xl bg-slate-50 px-4 py-3">
              <p class="text-slate-500">Purchasing Order</p>
              <p class="font-medium text-slate-900">{{ detail.purchasing_order?.nomor_po ?? '-' }}</p>
            </div>
            <div class="rounded-2xl bg-slate-50 px-4 py-3">
              <p class="text-slate-500">Surat Jalan</p>
              <p class="font-medium text-slate-900">{{ detail.surat_jalan?.nomor ?? '-' }}</p>
            </div>
            <div class="rounded-2xl bg-slate-50 px-4 py-3">
              <p class="text-slate-500">Berita Acara</p>
              <p class="font-medium text-slate-900">{{ detail.berita_acara?.nomor ?? '-' }}</p>
            </div>
            <div class="rounded-2xl bg-slate-50 px-4 py-3">
              <p class="text-slate-500">Faktur Pajak</p>
              <p class="font-medium text-slate-900">{{ detail.faktur_pajak?.dokumen_name ?? '-' }}</p>
            </div>
          </div>
        </article>
      </section>

      <section v-if="detail" class="panel p-6">
        <h3 class="text-lg font-semibold text-slate-900">Item Penawaran</h3>
        <div class="mt-4 overflow-x-auto">
          <table class="w-full min-w-[900px] border-collapse text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
              <tr>
                <th class="px-5 py-4 font-medium">Item</th>
                <th class="px-5 py-4 font-medium">Rincian</th>
                <th class="px-5 py-4 font-medium">Qty</th>
                <th class="px-5 py-4 font-medium">Satuan</th>
                <th class="px-5 py-4 font-medium">Harga</th>
                <th class="px-5 py-4 font-medium">Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in detail.penawaran?.items ?? []" :key="item.id ?? item.nama" class="border-t border-slate-200">
                <td class="px-5 py-4 font-medium text-slate-900">{{ item.nama }}</td>
                <td class="px-5 py-4 text-slate-600">{{ item.rincian || '-' }}</td>
                <td class="px-5 py-4 text-slate-600">{{ item.qty }}</td>
                <td class="px-5 py-4 text-slate-600">{{ item.satuan }}</td>
                <td class="px-5 py-4 text-slate-600">{{ formatCurrency(item.unit_price) }}</td>
                <td class="px-5 py-4 text-slate-600">{{ formatCurrency(item.amount) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <section v-if="actionMessage || actionError" class="panel p-6">
        <div v-if="actionMessage" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
          {{ actionMessage }}
        </div>
        <div v-if="actionError" class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          {{ actionError }}
        </div>
      </section>

      <section class="flex flex-col gap-3 sm:flex-row sm:justify-between">
        <a href="/invoice" class="button-ghost justify-center">Kembali ke daftar</a>
        <div class="flex flex-col gap-3 sm:flex-row">
          <button type="button" class="button-ghost justify-center" :disabled="busy" @click="sendEmail">
            {{ busy ? 'Memproses...' : 'Kirim Email' }}
          </button>
          <a :href="pdfLink" target="_blank" class="button-primary justify-center">Buka PDF</a>
        </div>
      </section>
    </div>
  </DefaultLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import DefaultLayout from '../../layouts/DefaultLayout.vue'
import api, { extractApiMessage } from '../../utils/api'
import { formatCurrency, formatDate } from '../../utils/format'

const props = defineProps({
  invoiceId: {
    type: [Number, String],
    required: true,
  },
})

const detail = ref(null)
const busy = ref(false)
const actionMessage = ref('')
const actionError = ref('')

const response = await api.get(`/api/invoices/${props.invoiceId}`)
detail.value = response.data?.data ?? null

const pdfLink = computed(() => `/invoice/${props.invoiceId}/pdf?download=1`)

function paymentLabel(status) {
  return status === 'paid' ? 'Sudah Dibayarkan' : 'Belum Dibayarkan'
}

async function sendEmail() {
  if (!detail.value || busy.value) return

  busy.value = true
  actionMessage.value = ''
  actionError.value = ''

  try {
    const response = await api.post(`/api/invoices/${detail.value.id}/send`)
    actionMessage.value = response.data?.message ?? 'Invoice berhasil dikirim.'
  } catch (error) {
    actionError.value = extractApiMessage(error, 'Gagal mengirim invoice.')
  } finally {
    busy.value = false
  }
}
</script>
