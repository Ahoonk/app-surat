<template>
  <DefaultLayout>
    <Head title="Nota Toko" />

    <div class="space-y-6">
      <section class="panel p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Modul</p>
            <h2 class="section-title mt-2">Nota Toko</h2>
            <p class="mt-3 text-sm text-slate-600">
              Daftar nota toko, status pembayaran, dan aksi kirim atau verifikasi pembayaran.
            </p>
          </div>
          <a href="/nota-toko/create" class="button-primary w-full sm:w-auto">+ Buat Nota Toko</a>
        </div>
      </section>

      <section v-if="flash.success || flash.error || flash.status" class="panel p-6">
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

      <section class="panel overflow-hidden">
        <div class="hidden md:block overflow-x-auto">
          <table class="w-full min-w-[1100px] border-collapse text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
              <tr>
                <th class="px-5 py-4 font-medium">Nomor</th>
                <th class="px-5 py-4 font-medium">Tanggal</th>
                <th class="px-5 py-4 font-medium">Customer</th>
                <th class="px-5 py-4 font-medium">Total</th>
                <th class="px-5 py-4 font-medium">Status Bayar</th>
                <th class="px-5 py-4 font-medium">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in notaTokos" :key="item.id" class="border-t border-slate-200">
                <td class="px-5 py-4 font-medium text-slate-900">{{ item.nomor }}</td>
                <td class="px-5 py-4 text-slate-600">{{ formatDate(item.tanggal) }}</td>
                <td class="px-5 py-4 text-slate-600">{{ item.customer_nama }}</td>
                <td class="px-5 py-4 text-slate-600">{{ formatCurrency(item.total) }}</td>
                <td class="px-5 py-4 text-slate-600">
                  <div :class="item.payment_status === 'paid' ? 'text-emerald-600' : 'text-amber-600'" class="font-medium">
                    {{ item.payment_status === 'paid' ? 'Sudah Dibayar' : 'Belum Dibayar' }}
                  </div>
                  <div v-if="item.payment_date" class="text-xs text-slate-500">
                    {{ formatDate(item.payment_date) }}
                  </div>
                </td>
                <td class="px-5 py-4">
                  <div class="flex flex-wrap gap-2">
                    <a :href="`/nota-toko/${item.id}`" class="button-ghost px-3 py-2 text-sm">Preview</a>
                    <a :href="`/nota-toko/${item.id}/edit`" class="button-ghost px-3 py-2 text-sm">Edit</a>

                    <form :action="`/nota-toko/${item.id}/send`" method="POST" @submit="confirmSubmit($event, 'Kirim nota toko ke email customer?')">
                      <input type="hidden" name="_token" :value="csrfToken" />
                      <button type="submit" class="button-ghost px-3 py-2 text-sm">Kirim</button>
                    </form>

                    <button
                      v-if="canVerify && item.payment_status !== 'paid'"
                      type="button"
                      class="button-primary px-3 py-2 text-sm"
                      @click="openVerify(item)"
                    >
                      Verifikasi
                    </button>

                    <form :action="`/nota-toko/${item.id}`" method="POST" @submit="confirmSubmit($event, 'Hapus nota toko ini?')">
                      <input type="hidden" name="_token" :value="csrfToken" />
                      <input type="hidden" name="_method" value="DELETE" />
                      <button type="submit" class="button-ghost px-3 py-2 text-sm text-red-600">Hapus</button>
                    </form>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="grid gap-4 p-4 md:hidden">
          <article
            v-for="item in notaTokos"
            :key="item.id"
            class="rounded-[24px] border border-slate-200 bg-white p-4 shadow-sm"
          >
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <p class="text-[11px] uppercase tracking-[0.28em] text-slate-500">Nota Toko</p>
                <h3 class="mt-1 truncate text-base font-semibold text-slate-900">{{ item.nomor }}</h3>
                <p class="mt-1 text-xs text-slate-500">{{ formatDate(item.tanggal) }}</p>
              </div>
              <div
                class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold"
                :class="item.payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'"
              >
                {{ item.payment_status === 'paid' ? 'Sudah Dibayar' : 'Belum Dibayar' }}
              </div>
            </div>

            <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
              <div class="rounded-2xl bg-slate-50 p-3">
                <dt class="text-[11px] uppercase tracking-[0.2em] text-slate-500">Customer</dt>
                <dd class="mt-1 font-medium text-slate-900">{{ item.customer_nama }}</dd>
              </div>
              <div class="rounded-2xl bg-slate-50 p-3">
                <dt class="text-[11px] uppercase tracking-[0.2em] text-slate-500">Total</dt>
                <dd class="mt-1 font-medium text-slate-900">{{ formatCurrency(item.total) }}</dd>
              </div>
            </dl>

            <div v-if="item.payment_date" class="mt-3 text-xs text-slate-500">
              Dibayar: {{ formatDate(item.payment_date) }}
            </div>

            <div class="mt-4 grid grid-cols-2 gap-2">
              <a :href="`/nota-toko/${item.id}`" class="button-ghost w-full px-3 py-2 text-sm">Preview</a>
              <a :href="`/nota-toko/${item.id}/edit`" class="button-ghost w-full px-3 py-2 text-sm">Edit</a>

              <form :action="`/nota-toko/${item.id}/send`" method="POST" class="w-full" @submit="confirmSubmit($event, 'Kirim nota toko ke email customer?')">
                <input type="hidden" name="_token" :value="csrfToken" />
                <button type="submit" class="button-ghost w-full px-3 py-2 text-sm">Kirim</button>
              </form>

              <form :action="`/nota-toko/${item.id}`" method="POST" class="col-span-2 w-full" @submit="confirmSubmit($event, 'Hapus nota toko ini?')">
                <input type="hidden" name="_token" :value="csrfToken" />
                <input type="hidden" name="_method" value="DELETE" />
                <button type="submit" class="button-ghost w-full px-3 py-2 text-sm text-red-600">Hapus</button>
              </form>
            </div>

            <button
              v-if="canVerify && item.payment_status !== 'paid'"
              type="button"
              class="button-primary mt-3 w-full px-3 py-2 text-sm"
              @click="openVerify(item)"
            >
              Verifikasi
            </button>
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
  notaTokos: {
    type: Array,
    default: () => [],
  },
})

const page = usePage()
const csrfToken = computed(() => page.props.csrfToken)
const flash = computed(() => page.props.flash ?? {})
const canVerify = computed(() => ['admin', 'superadmin'].includes(page.props.auth?.user?.role ?? ''))

const verifyTarget = ref(null)
const verifyForm = reactive({
  payment_date: new Date().toISOString().slice(0, 10),
})

const verifyAction = computed(() =>
  verifyTarget.value ? `/nota-toko/${verifyTarget.value.id}/verify-payment` : '/nota-toko'
)

function openVerify(item) {
  verifyTarget.value = item
  verifyForm.payment_date = new Date().toISOString().slice(0, 10)
}

function closeVerify() {
  verifyTarget.value = null
}

function confirmSubmit(event, message) {
  if (!confirm(message)) {
    event.preventDefault()
  }
}
</script>
