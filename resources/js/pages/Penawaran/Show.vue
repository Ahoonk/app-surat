<template>
  <DefaultLayout>
    <div class="space-y-6">
      <section class="panel p-6" v-if="penawaran">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
          <div>
            <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Detail Penawaran</p>
            <h2 class="section-title mt-2">{{ penawaran.nomor }}</h2>
            <p class="mt-2 text-sm text-slate-600">{{ penawaran.to_company || penawaran.customer_nama }}</p>
            <p class="mt-1 text-sm text-slate-500">{{ formatDate(penawaran.tanggal) }}</p>
          </div>
          <div class="flex flex-wrap gap-2">
            <a :href="`/penawaran/${penawaran.id}/pdf?download=1`" target="_blank" class="button-ghost px-4 py-2">PDF</a>
            <a :href="`/penawaran/${penawaran.id}/edit`" class="button-ghost px-4 py-2">Edit</a>
            <button type="button" class="button-primary px-4 py-2" :disabled="sending" @click="sendEmail">
              {{ sending ? 'Memproses...' : 'Kirim Email' }}
            </button>
          </div>
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

      <section v-if="penawaran" class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <article class="panel p-6">
          <h3 class="text-lg font-semibold text-slate-900">Item Penawaran</h3>
          <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[800px] border-collapse text-sm">
              <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                  <th class="px-4 py-3 font-medium">Item</th>
                  <th class="px-4 py-3 font-medium">Qty</th>
                  <th class="px-4 py-3 font-medium">Satuan</th>
                  <th class="px-4 py-3 font-medium">Harga</th>
                  <th class="px-4 py-3 font-medium">Amount</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in penawaran.items ?? []" :key="item.id" class="border-t border-slate-200">
                  <td class="px-4 py-3 text-slate-700">
                    <div class="font-medium text-slate-900">{{ item.nama }}</div>
                    <div v-if="item.rincian" class="text-xs text-slate-500">{{ item.rincian }}</div>
                  </td>
                  <td class="px-4 py-3 text-slate-600">{{ item.qty }}</td>
                  <td class="px-4 py-3 text-slate-600">{{ item.satuan }}</td>
                  <td class="px-4 py-3 text-slate-600">{{ formatCurrency(item.unit_price) }}</td>
                  <td class="px-4 py-3 text-slate-600">{{ formatCurrency(item.amount) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </article>

        <aside class="space-y-6">
          <article class="panel p-6">
            <h3 class="text-lg font-semibold text-slate-900">Ringkasan</h3>
            <div class="mt-4 space-y-3">
              <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                <span class="text-sm text-slate-500">Subtotal</span>
                <span class="font-semibold text-slate-900">{{ formatCurrency(penawaran.subtotal) }}</span>
              </div>
              <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                <span class="text-sm text-slate-500">Pajak</span>
                <span class="font-semibold text-slate-900">{{ formatCurrency(penawaran.tax_amount) }}</span>
              </div>
              <div class="flex items-center justify-between rounded-2xl bg-cyan-50 px-4 py-4">
                <span class="text-sm font-medium text-cyan-800">Total</span>
                <span class="text-xl font-semibold text-cyan-950">{{ formatCurrency(penawaran.total) }}</span>
              </div>
            </div>
          </article>

          <article class="panel p-6">
            <h3 class="text-lg font-semibold text-slate-900">Invoice Turunan</h3>
            <div class="mt-4 space-y-3">
              <div v-for="invoice in penawaran.invoices ?? []" :key="invoice.id" class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <p class="font-medium text-slate-900">{{ invoice.nomor }}</p>
                    <p class="text-xs text-slate-500">{{ formatDate(invoice.tanggal) }}</p>
                  </div>
                  <span :class="invoice.payment_status === 'paid' ? 'text-emerald-600' : 'text-amber-600'" class="text-xs font-semibold uppercase">
                    {{ invoice.payment_status === 'paid' ? 'Paid' : 'Unpaid' }}
                  </span>
                </div>
                <div class="mt-3 text-sm text-slate-600">
                  <a :href="`/invoice/${invoice.id}`" class="text-cyan-700 font-medium">Buka invoice</a>
                </div>
              </div>
            </div>
          </article>
        </aside>
      </section>
    </div>
  </DefaultLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import DefaultLayout from '../../layouts/DefaultLayout.vue'
import api, { extractApiMessage } from '../../utils/api'
import { formatCurrency, formatDate } from '../../utils/format'

const props = defineProps({
  penawaranId: {
    type: [Number, String],
    required: true,
  },
})

const penawaran = ref(null)
const actionMessage = ref('')
const actionError = ref('')
const sending = ref(false)

const response = await api.get(`/api/penawarans/${props.penawaranId}`)
penawaran.value = response.data?.data ?? null

async function sendEmail() {
  if (!penawaran.value || sending.value) return

  sending.value = true
  actionMessage.value = ''
  actionError.value = ''

  try {
    const response = await api.post(`/api/penawarans/${penawaran.value.id}/send`)
    actionMessage.value = response.data?.message ?? 'Surat penawaran berhasil dikirim.'
  } catch (error) {
    actionError.value = extractApiMessage(error, 'Gagal mengirim penawaran.')
  } finally {
    sending.value = false
  }
}
</script>
