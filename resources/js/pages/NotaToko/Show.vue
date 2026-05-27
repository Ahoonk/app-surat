<template>
  <DefaultLayout>
    <Head :title="notaToko.nomor" />

    <div class="space-y-6">
      <section class="panel p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
          <div>
            <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Preview Nota Toko</p>
            <h2 class="section-title mt-2">{{ notaToko.nomor }}</h2>
            <p class="mt-2 text-sm text-slate-600">{{ formatDate(notaToko.tanggal) }}</p>
          </div>

          <div class="flex flex-col gap-3 sm:flex-row">
            <a :href="pdfLink" target="_blank" class="button-primary w-full sm:w-auto">Export PDF</a>
            <a href="/nota-toko" class="button-ghost w-full sm:w-auto">Kembali</a>
          </div>
        </div>
      </section>

      <section class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <article class="panel p-6">
          <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h3 class="text-lg font-semibold text-slate-900">Status Pembayaran</h3>
              <p class="mt-1 text-sm text-slate-500">Status ini bisa diverifikasi oleh admin atau superadmin.</p>
            </div>

            <div>
              <p :class="notaToko.payment_status === 'paid' ? 'text-emerald-600' : 'text-amber-600'" class="text-base font-semibold">
                {{ notaToko.payment_status === 'paid' ? 'Sudah Dibayar' : 'Belum Dibayar' }}
              </p>
              <p v-if="notaToko.payment_date" class="text-xs text-slate-500">{{ formatDate(notaToko.payment_date) }}</p>
            </div>
          </div>
        </article>

        <article class="panel p-6">
          <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
              <h3 class="text-lg font-semibold text-slate-900">Aksi Cepat</h3>
              <p class="mt-1 text-sm text-slate-500">Edit, kirim email, atau verifikasi pembayaran dari sini.</p>
            </div>

            <button
              v-if="canVerify && notaToko.payment_status !== 'paid'"
              type="button"
              class="button-primary w-full sm:w-auto"
              @click="openVerify"
            >
              Verifikasi Bayar
            </button>
          </div>

          <div class="mt-5 grid gap-3 sm:grid-cols-2">
            <a :href="`/nota-toko/${notaToko.id}/edit`" class="button-ghost w-full">Edit</a>
            <form :action="`/nota-toko/${notaToko.id}/send`" method="POST" class="w-full" @submit="confirmSend">
              <input type="hidden" name="_token" :value="csrfToken" />
              <button type="submit" class="button-ghost w-full">Kirim Email</button>
            </form>
          </div>
        </article>
      </section>

      <section class="panel p-6">
        <h3 class="text-lg font-semibold text-slate-900">Detail Transaksi</h3>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          <div class="rounded-2xl bg-slate-50 p-4">
            <p class="text-sm text-slate-500">Customer</p>
            <p class="mt-1 font-medium text-slate-900">{{ notaToko.customer_nama }}</p>
          </div>
          <div class="rounded-2xl bg-slate-50 p-4">
            <p class="text-sm text-slate-500">Alamat</p>
            <p class="mt-1 font-medium text-slate-900 whitespace-pre-line">{{ notaToko.alamat || '-' }}</p>
          </div>
          <div class="rounded-2xl bg-slate-50 p-4">
            <p class="text-sm text-slate-500">Subtotal</p>
            <p class="mt-1 font-medium text-slate-900">{{ formatCurrency(notaToko.subtotal) }}</p>
          </div>
          <div class="rounded-2xl bg-slate-50 p-4">
            <p class="text-sm text-slate-500">Total</p>
            <p class="mt-1 font-medium text-slate-900">{{ formatCurrency(notaToko.total) }}</p>
          </div>
        </div>
      </section>

      <section class="panel p-6">
        <h3 class="text-lg font-semibold text-slate-900">Item Nota</h3>
        <div class="mt-4 hidden md:block overflow-x-auto">
          <table class="w-full min-w-[900px] border-collapse text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
              <tr>
                <th class="px-5 py-4 font-medium">No</th>
                <th class="px-5 py-4 font-medium">Item</th>
                <th class="px-5 py-4 font-medium">Qty</th>
                <th class="px-5 py-4 font-medium">Satuan</th>
                <th class="px-5 py-4 font-medium">Unit Price</th>
                <th class="px-5 py-4 font-medium">Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in notaToko.items ?? []" :key="item.id ?? `${item.nama}-${item.qty}`" class="border-t border-slate-200">
                <td class="px-5 py-4 text-slate-600">{{ index + 1 }}</td>
                <td class="px-5 py-4 font-medium text-slate-900">{{ item.nama }}</td>
                <td class="px-5 py-4 text-slate-600">{{ formatQty(item.qty) }}</td>
                <td class="px-5 py-4 text-slate-600">{{ item.satuan }}</td>
                <td class="px-5 py-4 text-slate-600">{{ formatCurrency(item.unit_price) }}</td>
                <td class="px-5 py-4 text-slate-600">{{ formatCurrency(item.amount) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="mt-4 grid gap-4 md:hidden">
          <article
            v-for="(item, index) in notaToko.items ?? []"
            :key="item.id ?? `${item.nama}-${item.qty}`"
            class="rounded-[24px] border border-slate-200 bg-white p-4 shadow-sm"
          >
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <p class="text-[11px] uppercase tracking-[0.28em] text-slate-500">Item {{ index + 1 }}</p>
                <h4 class="mt-1 break-words text-base font-semibold text-slate-900">{{ item.nama }}</h4>
                <p class="mt-1 text-xs text-slate-500">{{ item.satuan }}</p>
              </div>
              <div class="shrink-0 rounded-full bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-700">
                {{ formatCurrency(item.amount) }}
              </div>
            </div>

            <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
              <div class="rounded-2xl bg-slate-50 p-3">
                <dt class="text-[11px] uppercase tracking-[0.2em] text-slate-500">Qty</dt>
                <dd class="mt-1 font-medium text-slate-900">{{ formatQty(item.qty) }}</dd>
              </div>
              <div class="rounded-2xl bg-slate-50 p-3">
                <dt class="text-[11px] uppercase tracking-[0.2em] text-slate-500">Unit Price</dt>
                <dd class="mt-1 font-medium text-slate-900">{{ formatCurrency(item.unit_price) }}</dd>
              </div>
            </dl>
          </article>
        </div>
      </section>
    </div>

    <div v-if="verifyTarget" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="closeVerify">
      <div class="w-full max-w-md rounded-[24px] bg-white p-6 shadow-2xl">
        <h3 class="text-lg font-semibold text-slate-900">Verifikasi Pembayaran Nota Toko</h3>
        <p class="mt-2 text-sm text-slate-500">Pilih tanggal pembayaran untuk mengubah status menjadi lunas.</p>

        <form class="mt-5 space-y-4" :action="verifyAction" method="POST">
          <input type="hidden" name="_token" :value="csrfToken" />
          <label class="block">
            <span class="mb-2 block text-sm font-medium text-slate-700">Tanggal Pembayaran</span>
            <input v-model="verifyForm.payment_date" type="date" name="payment_date" class="input" required />
          </label>

          <div class="flex justify-end gap-3">
            <button type="button" class="button-ghost" @click="closeVerify">Batal</button>
            <button type="submit" class="button-primary">Submit</button>
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
import { formatCurrency, formatDate } from '../../utils/format'

const props = defineProps({
  notaToko: {
    type: Object,
    required: true,
  },
})

const page = usePage()
const csrfToken = computed(() => page.props.csrfToken)
const canVerify = computed(() => ['admin', 'superadmin'].includes(page.props.auth?.user?.role ?? ''))
const verifyTarget = ref(false)
const verifyForm = reactive({
  payment_date: new Date().toISOString().slice(0, 10),
})

const pdfLink = computed(() => `/nota-toko/${props.notaToko.id}/pdf?download=1`)
const verifyAction = computed(() => `/nota-toko/${props.notaToko.id}/verify-payment`)

function openVerify() {
  verifyTarget.value = true
  verifyForm.payment_date = new Date().toISOString().slice(0, 10)
}

function closeVerify() {
  verifyTarget.value = false
}

function confirmSend(event) {
  if (!confirm('Kirim nota toko ke email customer?')) {
    event.preventDefault()
  }
}

function formatQty(value) {
  const amount = Number(value ?? 0)
  return Number.isInteger(amount) ? String(amount) : amount.toFixed(2).replace(/\.00$/, '')
}

</script>
