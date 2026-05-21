<template>
  <DefaultLayout>
    <div class="space-y-6">
      <section class="panel p-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
          <div>
            <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Modul</p>
            <h2 class="section-title mt-2">Surat Penawaran</h2>
            <p class="mt-3 text-sm text-slate-600">Daftar penawaran yang tersambung ke backend. Dari sini kamu bisa masuk ke detail, edit, atau kirim email.</p>
          </div>
          <div class="flex flex-col items-start gap-3 sm:items-end">
            <a href="/penawaran/create" class="button-primary">
              + Buat Penawaran
            </a>
            <div class="text-sm text-slate-500">
              {{ rows.length }} data
            </div>
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

      <section class="panel overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full min-w-[900px] border-collapse text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
              <tr>
                <th class="px-5 py-4 font-medium">Nomor</th>
                <th class="px-5 py-4 font-medium">Tanggal</th>
                <th class="px-5 py-4 font-medium">Customer</th>
                <th class="px-5 py-4 font-medium">Status</th>
                <th class="px-5 py-4 font-medium">Total</th>
                <th class="px-5 py-4 font-medium">Invoice Terakhir</th>
                <th class="px-5 py-4 font-medium">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in rows" :key="item.id" class="border-t border-slate-200">
                <td class="px-5 py-4 font-medium text-slate-900">{{ item.nomor }}</td>
                <td class="px-5 py-4 text-slate-600">{{ formatDate(item.tanggal) }}</td>
                <td class="px-5 py-4 text-slate-600">{{ item.to_company || item.customer_nama }}</td>
                <td class="px-5 py-4 text-slate-600">{{ item.status }}</td>
                <td class="px-5 py-4 text-slate-600">{{ formatCurrency(item.total) }}</td>
                <td class="px-5 py-4 text-slate-600">
                  <span v-if="item.latest_invoice">{{ item.latest_invoice.nomor }}</span>
                  <span v-else>-</span>
                </td>
                <td class="px-5 py-4 text-slate-600">
                  <div class="flex flex-wrap gap-2">
                    <a :href="`/penawaran/${item.id}`" class="button-ghost px-3 py-2 text-sm">
                      Detail
                    </a>
                    <a :href="`/penawaran/${item.id}/edit`" class="button-ghost px-3 py-2 text-sm">
                      Edit
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

const rows = ref([])
const busyActionId = ref(null)
const actionMessage = ref('')
const actionError = ref('')

async function loadRows() {
  const response = await api.get('/api/penawarans')
  rows.value = response.data?.data ?? []
}

await loadRows()

async function sendEmail(item) {
  if (busyActionId.value !== null) return

  busyActionId.value = item.id
  actionMessage.value = ''
  actionError.value = ''

  try {
    const response = await api.post(`/api/penawarans/${item.id}/send`)
    actionMessage.value = response.data?.message ?? 'Surat penawaran berhasil dikirim.'
  } catch (error) {
    actionError.value = extractApiMessage(error, 'Gagal mengirim penawaran.')
  } finally {
    busyActionId.value = null
  }
}
</script>
