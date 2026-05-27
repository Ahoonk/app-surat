<template>
  <DefaultLayout>
    <div class="space-y-6">
      <section v-if="detail" class="panel overflow-hidden">
        <div class="grid gap-6 p-6 lg:grid-cols-[1.1fr_0.9fr]">
          <div>
            <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Detail Berita Acara</p>
            <h2 class="section-title mt-3">{{ detail.nomor }}</h2>
            <p class="mt-2 text-sm text-slate-600">
              Invoice {{ detail.invoice?.nomor ?? '-' }} · {{ formatDate(detail.tanggal) }}
            </p>
          </div>
          <div class="rounded-[22px] bg-gradient-to-br from-slate-950 to-cyan-900 p-5 text-white">
            <p class="text-xs uppercase tracking-[0.3em] text-cyan-200/70">Perihal</p>
            <h3 class="mt-3 break-words text-2xl font-semibold">{{ detail.perihal || 'Berita Acara' }}</h3>
            <p class="mt-2 text-sm text-slate-200/80">{{ detail.kota_tanggal_manual || '-' }}</p>
          </div>
        </div>
      </section>

      <section v-if="detail" class="grid gap-6 lg:grid-cols-[1fr_0.9fr]">
        <article class="panel p-6">
          <h3 class="text-lg font-semibold text-slate-900">Keterangan</h3>
          <div class="mt-4 space-y-3 text-sm">
            <div class="rounded-2xl bg-slate-50 px-4 py-3">
              <p class="text-slate-500">Perihal</p>
              <p class="font-medium text-slate-900">{{ detail.perihal || '-' }}</p>
            </div>
            <div class="rounded-2xl bg-slate-50 px-4 py-3">
              <p class="text-slate-500">Keterangan Akhir</p>
              <p class="font-medium text-slate-900 whitespace-pre-line">{{ detail.keterangan_akhir || '-' }}</p>
            </div>
            <div class="rounded-2xl bg-slate-50 px-4 py-3">
              <p class="text-slate-500">Kota/Tanggal Manual</p>
              <p class="font-medium text-slate-900">{{ formatDate(detail.kota_tanggal_manual) }}</p>
            </div>
          </div>
        </article>

        <article class="panel p-6">
          <h3 class="text-lg font-semibold text-slate-900">Dokumen Terkait</h3>
          <div class="mt-4 space-y-3 text-sm">
            <div class="rounded-2xl bg-slate-50 px-4 py-3">
              <p class="text-slate-500">Purchasing Order</p>
              <p class="font-medium text-slate-900">{{ detail.invoice?.purchasing_order?.nomor_po ?? '-' }}</p>
            </div>
            <div class="rounded-2xl bg-slate-50 px-4 py-3">
              <p class="text-slate-500">Surat Jalan</p>
              <p class="font-medium text-slate-900">{{ detail.invoice?.surat_jalan?.nomor ?? '-' }}</p>
            </div>
          </div>
        </article>
      </section>

      <section v-if="detail?.invoice?.penawaran" class="panel p-6">
        <h3 class="text-lg font-semibold text-slate-900">Item Penawaran</h3>
        <div class="mt-4 hidden lg:block overflow-x-auto">
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
              <tr v-for="item in detail.invoice.penawaran.items ?? []" :key="item.id ?? item.nama" class="border-t border-slate-200">
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

        <div class="mt-4 grid gap-4 lg:hidden">
          <article
            v-for="item in detail.invoice.penawaran.items ?? []"
            :key="item.id ?? item.nama"
            class="rounded-[24px] border border-slate-200 bg-white p-4 shadow-sm"
          >
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <p class="text-[11px] uppercase tracking-[0.28em] text-slate-500">Item</p>
                <h4 class="mt-1 break-words text-base font-semibold text-slate-900">{{ item.nama }}</h4>
                <p class="mt-1 text-xs text-slate-500">{{ item.rincian || '-' }}</p>
              </div>
              <div class="shrink-0 rounded-full bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-700">
                {{ formatCurrency(item.amount) }}
              </div>
            </div>

            <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
              <div class="rounded-2xl bg-slate-50 p-3">
                <dt class="text-[11px] uppercase tracking-[0.2em] text-slate-500">Qty</dt>
                <dd class="mt-1 font-medium text-slate-900">{{ item.qty }}</dd>
              </div>
              <div class="rounded-2xl bg-slate-50 p-3">
                <dt class="text-[11px] uppercase tracking-[0.2em] text-slate-500">Satuan</dt>
                <dd class="mt-1 font-medium text-slate-900">{{ item.satuan }}</dd>
              </div>
              <div class="rounded-2xl bg-slate-50 p-3">
                <dt class="text-[11px] uppercase tracking-[0.2em] text-slate-500">Harga</dt>
                <dd class="mt-1 font-medium text-slate-900">{{ formatCurrency(item.unit_price) }}</dd>
              </div>
              <div class="rounded-2xl bg-slate-50 p-3">
                <dt class="text-[11px] uppercase tracking-[0.2em] text-slate-500">Amount</dt>
                <dd class="mt-1 font-medium text-slate-900">{{ formatCurrency(item.amount) }}</dd>
              </div>
            </dl>
          </article>
        </div>
      </section>

      <section class="flex flex-col gap-3 sm:flex-row sm:justify-between">
        <a href="/berita-acara" class="button-ghost justify-center sm:w-auto">Kembali ke daftar</a>
        <div class="flex flex-col gap-3 sm:flex-row">
          <button type="button" class="button-ghost justify-center sm:w-auto" :disabled="busy" @click="sendEmail">
            {{ busy ? 'Memproses...' : 'Kirim Email' }}
          </button>
          <a :href="pdfLink" target="_blank" class="button-primary justify-center sm:w-auto">Buka PDF</a>
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
  beritaAcaraId: {
    type: [Number, String],
    required: true,
  },
})

const detail = ref(null)
const busy = ref(false)

const response = await api.get(`/api/berita-acaras/${props.beritaAcaraId}`)
detail.value = response.data?.data ?? null

const pdfLink = computed(() => `/berita-acara/${props.beritaAcaraId}/pdf?download=1`)

async function sendEmail() {
  if (!detail.value || busy.value) return

  busy.value = true

  try {
    await api.post(`/api/berita-acaras/${detail.value.id}/send`)
  } catch (error) {
    alert(extractApiMessage(error, 'Gagal mengirim berita acara.'))
  } finally {
    busy.value = false
  }
}
</script>
