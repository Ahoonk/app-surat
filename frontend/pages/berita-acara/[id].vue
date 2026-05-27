<template>
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
          <h3 class="mt-3 text-2xl font-semibold">{{ detail.perihal || 'Berita Acara' }}</h3>
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
    </section>

    <section class="flex flex-col gap-3 sm:flex-row sm:justify-between">
      <NuxtLink to="/berita-acara" class="button-ghost justify-center">
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
import { formatCurrency, formatDate } from '~/utils/format'
import type { BeritaAcaraDetail } from '~/types/api'

definePageMeta({
  middleware: 'auth',
})

const route = useRoute()
const backendBase = useRuntimeConfig().public.apiBase
const apiFetch = useApi()

const id = computed(() => String(route.params.id))
const { data } = await useAsyncData(`berita-acara-${id.value}`, () => apiFetch<{ data: BeritaAcaraDetail }>(`/api/berita-acaras/${id.value}`))
const detail = computed(() => data.value?.data ?? null)
const busy = ref(false)

const pdfLink = computed(() => `${backendBase}/berita-acara/${id.value}/pdf?download=1`)

async function sendEmail() {
  busy.value = true

  try {
    await apiFetch(`/api/berita-acaras/${id.value}/send`, {
      method: 'POST',
    })
  } catch (error: any) {
    alert(error?.data?.message ?? 'Gagal mengirim berita acara.')
  } finally {
    busy.value = false
  }
}
</script>
