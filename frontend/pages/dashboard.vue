<template>
  <div class="space-y-6">
    <section class="panel overflow-hidden">
      <div class="grid gap-6 p-6 lg:grid-cols-[1.2fr_0.8fr]">
        <div>
          <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Ringkasan</p>
          <h2 class="section-title mt-3">Dashboard Operasional Administrasi Dokumen Perusahaan</h2>
          <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600">
            Aplikasi Operasional untuk Memantau Alur Dokumen, Invoice, Pajak, dan Nota Toko.
          </p>

          <div class="mt-6 flex flex-wrap gap-3">
            <NuxtLink to="/penawaran" class="button-primary">
              Menu
            </NuxtLink>
          </div>
        </div>

        <div class="rounded-[22px] bg-gradient-to-br from-slate-950 to-cyan-900 p-5 text-white">
          <div class="flex items-start gap-4">
            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-[1.1rem] bg-white/95 p-2 shadow-lg shadow-black/10 ring-1 ring-white/60">
              <img
                :src="companyLogo"
                :alt="`${session?.company?.name ?? 'Company'} logo`"
                class="h-full w-full object-contain"
              >
            </div>
            <div class="min-w-0 flex-1">
              <p class="text-xs uppercase tracking-[0.3em] text-cyan-200/70">Company</p>
              <h3 class="mt-2 truncate text-2xl font-semibold">{{ session?.company?.name ?? '-' }}</h3>
              <p class="mt-2 text-sm leading-6 text-slate-200/80">{{ session?.company?.address ?? '-' }}</p>
            </div>
          </div>

          <div class="mt-5 grid grid-cols-2 gap-3">
            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 shadow-inner shadow-black/5">
              <p class="text-xs uppercase tracking-[0.25em] text-cyan-100/70">Customer</p>
              <p class="mt-2 text-xl font-semibold">{{ session?.lookups?.customers?.length ?? 0 }}</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 shadow-inner shadow-black/5">
              <p class="text-xs uppercase tracking-[0.25em] text-cyan-100/70">Mitra</p>
              <p class="mt-2 text-xl font-semibold">{{ session?.lookups?.mitras?.length ?? 0 }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      <article v-for="card in metricCards" :key="card.label" class="panel p-5">
        <p class="text-sm text-slate-500">{{ card.label }}</p>
        <p class="mt-3 text-3xl font-semibold text-slate-950">{{ card.value }}</p>
        <p class="mt-2 text-xs uppercase tracking-[0.28em]" :class="card.tone">{{ card.note }}</p>
      </article>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      <article v-for="card in workflowCards" :key="card.label" class="panel p-5">
        <div class="flex items-start justify-between gap-3">
          <div>
            <p class="text-sm text-slate-500">{{ card.label }}</p>
            <p class="mt-3 text-3xl font-semibold text-slate-950">{{ card.value }}</p>
          </div>
          <span class="rounded-full px-3 py-1 text-xs font-medium" :class="card.badgeClass">
            {{ card.badge }}
          </span>
        </div>
        <p class="mt-3 text-sm leading-6 text-slate-600">{{ card.note }}</p>
        <NuxtLink :to="card.to" class="mt-4 inline-flex text-sm font-medium text-cyan-700">
          Buka modul
        </NuxtLink>
      </article>
    </section>

    <section class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
      <article class="panel p-6">
        <div class="flex items-center justify-between gap-4">
          <div>
            <h3 class="text-lg font-semibold text-slate-900">Aktivitas terbaru</h3>
            <p class="text-sm text-slate-500">40 transaksi terakhir dari modul penawaran yang sudah terhubung ke invoice.</p>
          </div>
          <NuxtLink to="/penawaran" class="button-ghost text-sm">Lihat semua</NuxtLink>
        </div>

        <div class="mt-5 space-y-3">
          <div
            v-for="item in recentTransactions"
            :key="item.id"
            class="rounded-2xl border border-slate-200 bg-white p-4"
          >
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
              <div>
                <p class="font-semibold text-slate-900">{{ item.customer }}</p>
                <p class="text-sm text-slate-500">{{ item.nomor }} · {{ item.jenis }}</p>
                <p class="mt-1 text-xs text-slate-400">
                  Invoice {{ item.invoiceNumber }}
                  <span v-if="item.invoicePayment">{{ item.invoicePayment }}</span>
                  <span v-if="item.documentState"> · {{ item.documentState }}</span>
                </p>
              </div>
              <div class="flex flex-wrap items-center gap-2 text-sm">
                <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-600">{{ item.status }}</span>
                <span class="rounded-full bg-cyan-50 px-3 py-1 text-cyan-700">{{ formatCurrency(item.total) }}</span>
              </div>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
              <NuxtLink :to="`/penawaran/${item.id}`" class="button-ghost px-3 py-2 text-sm">
                Detail
              </NuxtLink>
              <a :href="item.pdfLink" target="_blank" class="button-ghost px-3 py-2 text-sm">
                PDF
              </a>
            </div>
          </div>
        </div>
      </article>

      <article class="panel p-6">
        <h3 class="text-lg font-semibold text-slate-900">Butuh perhatian</h3>
        <div class="mt-5 space-y-3">
          <div v-for="entry in attentionItems" :key="entry.label" class="rounded-2xl bg-slate-50 p-4">
            <div class="flex items-center justify-between gap-4">
              <div>
                <p class="text-sm text-slate-500">{{ entry.label }}</p>
                <p class="mt-1 text-2xl font-semibold text-slate-900">{{ entry.value }}</p>
              </div>
              <NuxtLink :to="entry.to" class="button-ghost px-3 py-2 text-xs">
                Lihat
              </NuxtLink>
            </div>
            <p class="mt-2 text-sm text-slate-600">{{ entry.note }}</p>
          </div>
        </div>
      </article>
    </section>
  </div>
</template>

<script setup lang="ts">
import { formatCurrency } from '~/utils/format'
import type { DashboardResponse } from '~/types/api'

definePageMeta({
  middleware: 'auth',
})

const { session, ensure } = useSession()
await useAsyncData('dashboard-bootstrap', () => ensure())

const dashboard = computed<DashboardResponse | null>(() => session.value?.dashboard ?? null)
const backendBase = useRuntimeConfig().public.apiBase
const companyLogo = computed(() => resolveCompanyLogo(session.value?.company?.logo))

const metricCards = computed(() => [
  {
    label: 'Total Invoice',
    value: formatCurrency(dashboard.value?.dashboardFinancial?.total_semua ?? 0),
    note: `${dashboard.value?.dashboardFinancial?.jumlah_semua ?? 0} transaksi`,
    tone: 'text-slate-500',
  },
  {
    label: 'Invoice Lunas',
    value: formatCurrency(dashboard.value?.dashboardFinancial?.total_sudah_dibayar ?? 0),
    note: `${dashboard.value?.dashboardFinancial?.jumlah_sudah_dibayar ?? 0} lunas`,
    tone: 'text-emerald-600',
  },
  {
    label: 'Invoice Belum Lunas',
    value: formatCurrency(dashboard.value?.dashboardFinancial?.total_belum_dibayar ?? 0),
    note: `${dashboard.value?.dashboardFinancial?.jumlah_belum_dibayar ?? 0} belum`,
    tone: 'text-amber-600',
  },
  {
    label: 'Nota Toko',
    value: formatCurrency(dashboard.value?.dashboardNotaToko?.total_semua ?? 0),
    note: `${dashboard.value?.dashboardNotaToko?.jumlah_semua ?? 0} nota`,
    tone: 'text-cyan-700',
  },
])

const workflowCards = computed(() => [
  {
    label: 'Penawaran Draft',
    value: dashboard.value?.dashboardStatus?.penawaran?.draft ?? 0,
    badge: 'Draft',
    badgeClass: 'bg-slate-100 text-slate-600',
    note: 'Penawaran yang masih bisa diedit sebelum dikirim atau diajukan.',
    to: '/penawaran',
  },
  {
    label: 'Menunggu PO',
    value: dashboard.value?.dashboardStatus?.purchasing_order?.menunggu_upload ?? 0,
    badge: 'Proses',
    badgeClass: 'bg-amber-100 text-amber-700',
    note: 'Penawaran approved yang belum punya dokumen PO.',
    to: '/penawaran',
  },
  {
    label: 'Invoice Unpaid',
    value: dashboard.value?.dashboardStatus?.invoice?.belum_dibayar ?? 0,
    badge: 'Tagihan',
    badgeClass: 'bg-rose-100 text-rose-700',
    note: 'Invoice yang masih menunggu pembayaran pelanggan.',
    to: '/invoice',
  },
  {
    label: 'Faktur Pending',
    value: dashboard.value?.dashboardStatus?.faktur_pajak?.menunggu_upload ?? 0,
    badge: 'Pajak',
    badgeClass: 'bg-cyan-100 text-cyan-700',
    note: 'Invoice yang belum memiliki faktur pajak tersambung.',
    to: '/invoice',
  },
])

const recentTransactions = computed(() => {
  const transactions = dashboard.value?.dashboardTransactions ?? []

  return transactions.slice(0, 6).map((row) => {
    const invoice = row.invoice
    const invoicePayment = invoice?.payment_status === 'paid' ? 'Sudah dibayar' : 'Belum dibayar'
    const documentState = row.faktur_pajak ? 'Faktur pajak tersedia' : 'Faktur pajak belum ada'

    return {
      id: row.penawaran.id,
      customer: row.penawaran.to_company || row.penawaran.customer_nama || '-',
      nomor: row.penawaran.nomor || '-',
      jenis: row.penawaran.jenis_kontrak || 'satuan',
      status: row.penawaran.status || '-',
      total: row.penawaran.total || 0,
      invoiceNumber: invoice?.nomor ?? '-',
      invoicePayment,
      documentState,
      pdfLink: `${backendBase}/penawaran/${row.penawaran.id}/pdf?download=1`,
    }
  })
})

const attentionItems = computed(() => [
  {
    label: 'Penawaran submitted',
    value: dashboard.value?.dashboardStatus?.penawaran?.submitted ?? 0,
    note: 'Perlu ditinjau sebelum masuk ke tahap approval.',
    to: '/penawaran',
  },
  {
    label: 'Approved tanpa PO',
    value: dashboard.value?.dashboardStatus?.purchasing_order?.menunggu_upload ?? 0,
    note: 'Siap diproses ke dokumen Purchasing Order.',
    to: '/penawaran',
  },
  {
    label: 'Faktur pajak menunggu',
    value: dashboard.value?.dashboardStatus?.faktur_pajak?.menunggu_upload ?? 0,
    note: 'Belum ada faktur pajak yang tersambung ke invoice.',
    to: '/invoice',
  },
])

function resolveCompanyLogo(logo: string | null | undefined) {
  const fallback = '/storage/logos/aldera.png'

  if (!logo) {
    return fallback
  }

  if (logo.startsWith('http://') || logo.startsWith('https://')) {
    return logo
  }

  const normalized = logo.replace(/^\/+/, '')

  if (normalized.startsWith('storage/')) {
    return `/${normalized}`
  }

  return `/storage/${normalized}`
}
</script>
