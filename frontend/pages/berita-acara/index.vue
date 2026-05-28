<template>
  <div class="space-y-6">
    <section class="panel p-6">
      <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
          <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Modul</p>
          <h2 class="section-title mt-2">Berita Acara</h2>
          <p class="mt-3 text-sm text-slate-600">Dokumen serah terima yang juga tersinkron dari invoice seperti perilaku backend lama.</p>
        </div>
        <div class="text-sm text-slate-500">
          {{ rows.length }} berita acara
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
      <div class="hidden lg:block overflow-x-auto">
        <table class="w-full min-w-[1100px] border-collapse text-sm">
          <thead class="bg-slate-50 text-left text-slate-500">
            <tr>
              <th class="px-5 py-4 font-medium">Nomor</th>
              <th class="px-5 py-4 font-medium">Tanggal</th>
              <th class="px-5 py-4 font-medium">Customer</th>
              <th class="px-5 py-4 font-medium">Invoice</th>
              <th class="px-5 py-4 font-medium">PO</th>
              <th class="px-5 py-4 font-medium">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in rows" :key="item.id" class="border-t border-slate-200">
              <td class="px-5 py-4 font-medium text-slate-900">{{ item.nomor }}</td>
              <td class="px-5 py-4 text-slate-600">{{ formatDate(item.tanggal) }}</td>
              <td class="px-5 py-4 text-slate-600">{{ item.invoice?.penawaran?.to_company || item.invoice?.penawaran?.customer_nama || '-' }}</td>
              <td class="px-5 py-4 text-slate-600">{{ item.invoice?.nomor ?? '-' }}</td>
              <td class="px-5 py-4 text-slate-600">{{ item.invoice?.purchasing_order?.nomor_po ?? '-' }}</td>
              <td class="px-5 py-4">
                <div class="flex flex-wrap gap-2">
                  <NuxtLink :to="`/berita-acara/${item.id}`" class="button-ghost px-3 py-2 text-sm">
                    Detail
                  </NuxtLink>
                  <a :href="pdfLink(item.id)" target="_blank" class="button-primary px-3 py-2 text-sm">
                    PDF
                  </a>
                  <button
                    type="button"
                    class="button-ghost px-3 py-2 text-sm"
                    :disabled="busyActionId === item.id"
                    @click="sendEmail(item)"
                  >
                    {{ busyActionId === item.id ? 'Memproses...' : 'Kirim Email' }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="grid gap-4 p-4 lg:hidden">
        <article
          v-for="item in rows"
          :key="item.id"
          class="rounded-[24px] border border-slate-200 bg-white p-4 shadow-sm"
        >
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <p class="text-[11px] uppercase tracking-[0.28em] text-slate-500">Berita Acara</p>
              <h3 class="mt-1 break-words text-base font-semibold text-slate-900">{{ item.nomor }}</h3>
              <p class="mt-1 text-xs text-slate-500">{{ formatDate(item.tanggal) }}</p>
            </div>
            <div class="shrink-0 rounded-full bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-700">
              {{ item.invoice?.nomor ?? '-' }}
            </div>
          </div>

          <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
            <div class="rounded-2xl bg-slate-50 p-3">
              <dt class="text-[11px] uppercase tracking-[0.2em] text-slate-500">Customer</dt>
              <dd class="mt-1 font-medium text-slate-900">
                {{ item.invoice?.penawaran?.to_company || item.invoice?.penawaran?.customer_nama || '-' }}
              </dd>
            </div>
            <div class="rounded-2xl bg-slate-50 p-3">
              <dt class="text-[11px] uppercase tracking-[0.2em] text-slate-500">PO</dt>
              <dd class="mt-1 font-medium text-slate-900">{{ item.invoice?.purchasing_order?.nomor_po ?? '-' }}</dd>
            </div>
          </dl>

          <div class="mt-4 grid grid-cols-2 gap-2">
            <NuxtLink :to="`/berita-acara/${item.id}`" class="button-ghost w-full px-3 py-2 text-sm">
              Detail
            </NuxtLink>
            <a :href="pdfLink(item.id)" target="_blank" class="button-primary w-full px-3 py-2 text-sm">
              PDF
            </a>
            <button
              type="button"
              class="button-ghost col-span-2 w-full px-3 py-2 text-sm"
              :disabled="busyActionId === item.id"
              @click="sendEmail(item)"
            >
              {{ busyActionId === item.id ? 'Memproses...' : 'Kirim Email' }}
            </button>
          </div>
        </article>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { formatDate } from '~/utils/format'
import type { ApiListResponse, BeritaAcaraSummary } from '~/types/api'

definePageMeta({
  middleware: 'auth',
})

const apiFetch = useApi()
const backendBase = useRuntimeConfig().public.apiBase

const { data } = await useAsyncData('berita-acaras', () => apiFetch<ApiListResponse<BeritaAcaraSummary>>('/api/berita-acaras'))
const rows = computed(() => data.value?.data ?? [])
const busyActionId = ref<number | null>(null)
const actionMessage = ref('')
const actionError = ref('')

function pdfLink(id: number): string {
  return `${backendBase}/berita-acara/${id}/pdf?download=1`
}

async function sendEmail(item: BeritaAcaraSummary) {
  if (busyActionId.value !== null) return

  busyActionId.value = item.id
  actionMessage.value = ''
  actionError.value = ''

  try {
    const response = await apiFetch<{ message?: string }>(`/api/berita-acaras/${item.id}/send`, {
      method: 'POST',
    })

    actionMessage.value = response.message ?? 'Berita acara berhasil dikirim.'
  } catch (error: any) {
    actionError.value = extractApiMessage(error, 'Gagal mengirim berita acara.')
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
