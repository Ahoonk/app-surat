<template>
  <DefaultLayout>
    <Head title="Purchasing Order" />

    <div class="space-y-6">
      <section class="panel p-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
          <div>
            <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Modul</p>
            <h2 class="section-title mt-2">Purchasing Order</h2>
            <p class="mt-3 text-sm text-slate-600">
              Upload dokumen PO, lalu lanjutkan ke invoice dari daftar dokumen yang sama.
            </p>
          </div>
          <div class="text-sm text-slate-500">
            {{ readyRows.length }} dokumen siap upload
          </div>
        </div>
      </section>

      <section v-if="flash.success || flash.status || flash.error" class="panel p-6">
        <div v-if="flash.success" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
          {{ flash.success }}
        </div>
        <div v-else-if="flash.status" class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
          {{ flash.status }}
        </div>
        <div v-if="flash.error" class="mt-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          {{ flash.error }}
        </div>
      </section>

      <section class="panel p-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
          <div>
            <h3 class="text-lg font-semibold text-slate-900">Penawaran Approved Siap Upload PO</h3>
            <p class="mt-1 text-sm text-slate-500">Isi dokumen dan nomor PO untuk masing-masing penawaran.</p>
          </div>
        </div>

        <div class="mt-6 space-y-4">
          <p v-if="readyRows.length === 0" class="text-sm text-slate-500">Belum ada penawaran approved yang menunggu upload PO.</p>

          <form
            v-for="penawaran in readyRows"
            :key="penawaran.id"
            class="grid gap-4 rounded-3xl border border-slate-200 bg-slate-50 p-5 xl:grid-cols-[1.3fr_0.9fr_0.9fr_0.9fr_auto]"
            action="/purchasing-order"
            method="POST"
            enctype="multipart/form-data"
          >
            <input type="hidden" name="_token" :value="csrfToken" />
            <input type="hidden" name="penawaran_id" :value="penawaran.id" />

            <div>
              <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Penawaran</p>
              <p class="mt-2 font-semibold text-slate-900">{{ penawaran.nomor }}</p>
              <p class="text-sm text-slate-500">{{ penawaran.to_company || penawaran.customer_nama }}</p>
            </div>

            <label class="block">
              <span class="mb-2 block text-sm font-medium text-slate-700">Dokumen PO</span>
              <input name="dokumen" type="file" accept=".pdf,.jpg,.jpeg,.png" class="input" required />
            </label>

            <label class="block">
              <span class="mb-2 block text-sm font-medium text-slate-700">Nomor PO</span>
              <input name="nomor_po" type="text" class="input" required />
            </label>

            <label class="block">
              <span class="mb-2 block text-sm font-medium text-slate-700">Tanggal PO</span>
              <input name="tanggal_po" type="date" class="input" required />
            </label>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-end">
              <button type="submit" class="button-primary w-full sm:w-auto">Upload</button>
              <button
                type="submit"
                :formaction="`/purchasing-order/${penawaran.id}/cancel`"
                formmethod="POST"
                formnovalidate
                class="button-ghost w-full sm:w-auto"
                @click="confirmAction($event, 'Batalkan approval dan kembalikan ke submitted?')"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
      </section>

      <section class="panel p-6">
        <div>
          <h3 class="text-lg font-semibold text-slate-900">Daftar Dokumen Purchasing Order</h3>
          <p class="mt-1 text-sm text-slate-500">Dokumen yang sudah diupload ditampilkan di sini bersama invoice terakhirnya.</p>
        </div>

        <div class="hidden lg:block mt-6 overflow-x-auto">
          <table class="w-full min-w-[1100px] border-collapse text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
              <tr>
                <th class="px-5 py-4 font-medium">Nomor Penawaran</th>
                <th class="px-5 py-4 font-medium">Jenis</th>
                <th class="px-5 py-4 font-medium">Dokumen PO</th>
                <th class="px-5 py-4 font-medium">Nomor PO</th>
                <th class="px-5 py-4 font-medium">Tanggal PO</th>
                <th class="px-5 py-4 font-medium">Invoice Terakhir</th>
                <th class="px-5 py-4 font-medium">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="penawaran in existingData" :key="penawaran.id" class="border-t border-slate-200">
                <td class="px-5 py-4 font-medium text-slate-900">{{ penawaran.nomor }}</td>
                <td class="px-5 py-4 capitalize text-slate-600">{{ penawaran.jenis_kontrak }}</td>
                <td class="px-5 py-4 text-slate-600">
                  <a
                    v-if="penawaran.purchasingOrder?.dokumen_path"
                    :href="storageLink(penawaran.purchasingOrder.dokumen_path)"
                    target="_blank"
                    class="text-cyan-700 hover:text-cyan-900"
                  >
                    {{ penawaran.purchasingOrder.dokumen_name }}
                  </a>
                  <span v-else>-</span>
                </td>
                <td class="px-5 py-4 text-slate-600">{{ penawaran.purchasingOrder?.nomor_po ?? '-' }}</td>
                <td class="px-5 py-4 text-slate-600">{{ formatDate(penawaran.purchasingOrder?.tanggal_po) }}</td>
                <td class="px-5 py-4 text-slate-600">
                  <template v-if="latestInvoice(penawaran)">
                    <div class="font-medium text-slate-900">{{ latestInvoice(penawaran).nomor }}</div>
                    <div class="text-xs text-slate-500">{{ formatDate(latestInvoice(penawaran).tanggal) }}</div>
                  </template>
                  <span v-else>-</span>
                </td>
                <td class="px-5 py-4 text-slate-600">
                  <template v-if="!latestInvoice(penawaran)">
                    <form :action="`/purchasing-order/${penawaran.id}/create-invoice`" method="POST">
                      <input type="hidden" name="_token" :value="csrfToken" />
                      <button type="submit" class="button-ghost px-3 py-2 text-sm">Cetak Invoice</button>
                    </form>
                  </template>
                  <template v-else-if="penawaran.jenis_kontrak === 'kontrak'">
                    <button type="button" class="button-ghost px-3 py-2 text-sm" @click="openNextInvoice(penawaran)">
                      Cetak Invoice Berikutnya
                    </button>
                  </template>
                  <span v-else class="font-medium text-emerald-600">Selesai</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="mt-6 grid gap-4 lg:hidden">
          <article
            v-for="penawaran in existingData"
            :key="penawaran.id"
            class="rounded-[24px] border border-slate-200 bg-white p-4 shadow-sm"
          >
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <p class="text-[11px] uppercase tracking-[0.28em] text-slate-500">Nomor Penawaran</p>
                <h4 class="mt-1 break-words text-base font-semibold text-slate-900">{{ penawaran.nomor }}</h4>
                <p class="mt-1 text-sm text-slate-500">{{ penawaran.jenis_kontrak }}</p>
              </div>
              <div class="shrink-0 rounded-full bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-700">
                {{ latestInvoice(penawaran) ? 'Ada Invoice' : 'Belum' }}
              </div>
            </div>

            <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
              <div class="rounded-2xl bg-slate-50 p-3">
                <dt class="text-[11px] uppercase tracking-[0.2em] text-slate-500">PO</dt>
                <dd class="mt-1 break-words font-medium text-slate-900">
                  {{ penawaran.purchasingOrder?.nomor_po ?? '-' }}
                </dd>
              </div>
              <div class="rounded-2xl bg-slate-50 p-3">
                <dt class="text-[11px] uppercase tracking-[0.2em] text-slate-500">Tanggal</dt>
                <dd class="mt-1 font-medium text-slate-900">
                  {{ formatDate(penawaran.purchasingOrder?.tanggal_po) }}
                </dd>
              </div>
              <div class="rounded-2xl bg-slate-50 p-3">
                <dt class="text-[11px] uppercase tracking-[0.2em] text-slate-500">Dokumen</dt>
                <dd class="mt-1 font-medium text-slate-900">
                  <a
                    v-if="penawaran.purchasingOrder?.dokumen_path"
                    :href="storageLink(penawaran.purchasingOrder.dokumen_path)"
                    target="_blank"
                    class="text-cyan-700"
                  >
                    {{ penawaran.purchasingOrder.dokumen_name }}
                  </a>
                  <span v-else>-</span>
                </dd>
              </div>
              <div class="rounded-2xl bg-slate-50 p-3">
                <dt class="text-[11px] uppercase tracking-[0.2em] text-slate-500">Invoice</dt>
                <dd class="mt-1 font-medium text-slate-900">
                  <template v-if="latestInvoice(penawaran)">
                    <div class="break-words">{{ latestInvoice(penawaran).nomor }}</div>
                    <div class="text-xs text-slate-500">{{ formatDate(latestInvoice(penawaran).tanggal) }}</div>
                  </template>
                  <span v-else>-</span>
                </dd>
              </div>
            </dl>

            <div class="mt-4">
              <template v-if="!latestInvoice(penawaran)">
                <form :action="`/purchasing-order/${penawaran.id}/create-invoice`" method="POST" class="w-full">
                  <input type="hidden" name="_token" :value="csrfToken" />
                  <button type="submit" class="button-ghost w-full px-3 py-2 text-sm">Cetak Invoice</button>
                </form>
              </template>
              <template v-else-if="penawaran.jenis_kontrak === 'kontrak'">
                <button type="button" class="button-ghost w-full px-3 py-2 text-sm" @click="openNextInvoice(penawaran)">
                  Cetak Invoice Berikutnya
                </button>
              </template>
              <span v-else class="block rounded-2xl bg-emerald-50 px-4 py-3 text-center text-sm font-medium text-emerald-700">
                Selesai
              </span>
            </div>
          </article>
        </div>
      </section>
    </div>

    <div
      v-if="nextInvoiceTarget"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
      @click.self="closeNextInvoice"
    >
      <div class="w-full max-w-md rounded-[24px] bg-white p-6 shadow-2xl">
        <h3 class="text-lg font-semibold text-slate-900">Cetak Invoice Berikutnya</h3>
        <p class="mt-2 text-sm text-slate-500">Invoice baru akan dibuat dari data penawaran dan PO yang sama.</p>

        <form class="mt-5 space-y-4" :action="nextInvoiceAction" method="POST">
          <input type="hidden" name="_token" :value="csrfToken" />
          <label class="block">
            <span class="mb-2 block text-sm font-medium text-slate-700">Tanggal Invoice</span>
            <input v-model="nextInvoiceForm.invoice_date" type="date" name="invoice_date" class="input" required />
          </label>

          <div class="flex justify-end gap-3">
            <button type="button" class="button-ghost" @click="closeNextInvoice">Batal</button>
            <button type="submit" class="button-primary">Submit &amp; Cetak</button>
          </div>
        </form>
      </div>
    </div>
  </DefaultLayout>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import DefaultLayout from '../../layouts/DefaultLayout.vue'
import { formatDate } from '../../utils/format'

const props = defineProps({
  approvedSatuan: {
    type: Array,
    default: () => [],
  },
  approvedKontrak: {
    type: Array,
    default: () => [],
  },
  existingData: {
    type: Array,
    default: () => [],
  },
})

const page = usePage()
const csrfToken = computed(() => page.props.csrfToken)
const flash = computed(() => page.props.flash ?? {})
const readyRows = computed(() => [...props.approvedSatuan, ...props.approvedKontrak])

const nextInvoiceTarget = ref(null)
const nextInvoiceForm = reactive({
  invoice_date: new Date().toISOString().slice(0, 10),
})

const nextInvoiceAction = computed(() =>
  nextInvoiceTarget.value ? `/purchasing-order/${nextInvoiceTarget.value.id}/next-invoice` : '/purchasing-order'
)

function storageLink(path) {
  return `/storage/${path}`
}

function latestInvoice(penawaran) {
  return penawaran.invoices?.[0] ?? null
}

function openNextInvoice(penawaran) {
  nextInvoiceTarget.value = penawaran
  nextInvoiceForm.invoice_date = new Date().toISOString().slice(0, 10)
}

function closeNextInvoice() {
  nextInvoiceTarget.value = null
}

function confirmAction(event, message) {
  if (!confirm(message)) {
    event.preventDefault()
  }
}
</script>
