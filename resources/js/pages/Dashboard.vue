<template>
    <Head :title="pageTitle" />

    <div class="relative overflow-hidden">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute left-[-8rem] top-[-6rem] h-72 w-72 rounded-full bg-cyan-400/15 blur-3xl"></div>
            <div class="absolute right-[-8rem] top-40 h-80 w-80 rounded-full bg-indigo-400/10 blur-3xl"></div>
            <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
            <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-red-500 shadow-2xl shadow-slate-950/30 backdrop-blur" data-aos="fade-up">
                <div class="grid gap-6 p-6 lg:grid-cols-[1.2fr_0.8fr] lg:p-8">
                    <div class="space-y-5">
                        <p class="text-xs uppercase tracking-[0.35em] text-cyan-200/70">Ringkasan Operasional</p>
                        <h1 class="max-w-3xl text-3xl font-bold leading-tight text-white sm:text-4xl lg:text-5xl">
                            Dashboard Operasional Administrasi Dokumen Perusahaan
                        </h1>
                        <p class="max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">
                            Aplikasi Operasional untuk Memantau Alur Dokumen, Invoice, Pajak, dan Nota Toko.
                        </p>

                        <div class="flex flex-wrap gap-3 pt-2">
                            <a href="/penawaran" class="inline-flex items-center rounded-full bg-cyan-400 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-cyan-300">
                                Menu
                            </a>
                        </div>
                    </div>

                    <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-5 text-white shadow-[0_18px_42px_rgba(2,6,23,0.25)]">
                        <div class="flex items-start gap-4">
                            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-[1.1rem] bg-white/95 p-2 shadow-lg shadow-black/10 ring-1 ring-white/60">
                                <img
                                    :src="companyLogo"
                                    :alt="`${companyName} logo`"
                                    class="h-full w-full object-contain"
                                >
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs uppercase tracking-[0.3em] text-cyan-200/60">Company</p>
                                <h2 class="mt-2 truncate text-2xl font-bold">{{ companyName }}</h2>
                            </div>
                        </div>

                        <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-300">
                            {{ companyAddress || 'Alamat perusahaan belum diisi.' }}
                        </p>

                        <div class="mt-5 grid grid-cols-2 gap-3">
                            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 shadow-inner shadow-black/5">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-300/70">Customer</p>
                                <p class="mt-2 text-2xl font-semibold">{{ customersCount }}</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 shadow-inner shadow-black/5">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-300/70">Mitra</p>
                                <p class="mt-2 text-2xl font-semibold">{{ mitrasCount }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <article v-for="card in metricCards" :key="card.label" class="rounded-[1.5rem] border border-slate-200/80 bg-white p-5 shadow-sm" data-aos="fade-up">
                    <p class="text-sm text-slate-500">{{ card.label }}</p>
                    <p class="mt-3 text-3xl font-bold text-slate-950">{{ card.value }}</p>
                    <p class="mt-2 text-xs uppercase tracking-[0.28em]" :class="card.tone">{{ card.note }}</p>
                </article>
            </section>

            <section class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <article v-for="card in workflowCards" :key="card.label" class="rounded-[1.5rem] border border-slate-200/80 bg-white p-5 shadow-sm" data-aos="fade-up">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm text-slate-500">{{ card.label }}</p>
                            <p class="mt-3 text-3xl font-bold text-slate-950">{{ card.value }}</p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-medium" :class="card.badgeClass">
                            {{ card.badge }}
                        </span>
                    </div>
                    <p class="mt-3 text-sm leading-6 text-slate-600">{{ card.note }}</p>
                    <a :href="card.to" class="mt-4 inline-flex text-sm font-semibold text-cyan-700">
                        Buka modul
                    </a>
                </article>
            </section>

            <section class="mt-6 grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                <article class="rounded-[1.5rem] border border-slate-200/80 bg-white p-6 shadow-sm" data-aos="fade-right">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-950">Aktivitas terbaru</h2>
                            <p class="text-sm text-slate-500">40 transaksi terakhir dari penawaran beserta invoice turunannya.</p>
                        </div>
                        <a href="/penawaran" class="inline-flex rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                            Lihat semua
                        </a>
                    </div>

                    <div class="mt-5 space-y-3">
                        <div
                            v-for="item in recentTransactions"
                            :key="item.id"
                            class="rounded-[1.25rem] border border-slate-200 bg-slate-50 p-4"
                        >
                            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <p class="font-semibold text-slate-950">{{ item.customer }}</p>
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
                                <a :href="item.detailLink" class="inline-flex rounded-full border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-white">
                                    Detail
                                </a>
                                <a :href="item.pdfLink" target="_blank" class="inline-flex rounded-full border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-white">
                                    PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="rounded-[1.5rem] border border-slate-200/80 bg-white p-6 shadow-sm" data-aos="fade-left">
                    <h2 class="text-lg font-semibold text-slate-950">Butuh perhatian</h2>
                    <div class="mt-5 space-y-3">
                        <div v-for="entry in attentionItems" :key="entry.label" class="rounded-[1.25rem] bg-slate-50 p-4">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm text-slate-500">{{ entry.label }}</p>
                                    <p class="mt-1 text-2xl font-bold text-slate-950">{{ entry.value }}</p>
                                </div>
                                <a :href="entry.to" class="inline-flex rounded-full border border-slate-200 px-3 py-2 text-xs font-medium text-slate-700 transition hover:bg-white">
                                    Lihat
                                </a>
                            </div>
                            <p class="mt-2 text-sm text-slate-600">{{ entry.note }}</p>
                        </div>
                    </div>
                </article>
            </section>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import { formatCurrency } from '../utils/format'

const props = defineProps({
    company: {
        type: Object,
        default: () => ({}),
    },
    customersCount: {
        type: Number,
        default: 0,
    },
    mitrasCount: {
        type: Number,
        default: 0,
    },
    dashboardFinancial: {
        type: Object,
        default: () => ({}),
    },
    dashboardStatus: {
        type: Object,
        default: () => ({}),
    },
    dashboardTax: {
        type: Object,
        default: () => ({}),
    },
    dashboardNotaToko: {
        type: Object,
        default: () => ({}),
    },
    dashboardTransactions: {
        type: Array,
        default: () => [],
    },
})

const page = usePage()
const appName = computed(() => page.props.appName || 'PT ASKARYA')
const pageTitle = computed(() => `Dashboard | ${appName.value}`)
const companyName = computed(() => props.company?.name || appName.value)
const companyAddress = computed(() => {
    if (props.company?.name === 'PT Aldera Saddatech Karya') {
        return 'Link. Acing Baru RT/RW 001/007, Kelurahan Masigit, Kecamatan Jombang\nKota Cilegon Provinsi Banten - 42414'
    }

    return props.company?.address || ''
})
const companyLogo = computed(() => resolveCompanyLogo(props.company?.logo))

function resolveCompanyLogo(logo) {
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

const metricCards = computed(() => [
    {
        label: 'Total Invoice',
        value: formatCurrency(props.dashboardFinancial?.total_semua ?? 0),
        note: `${props.dashboardFinancial?.jumlah_semua ?? 0} transaksi`,
        tone: 'text-slate-500',
    },
    {
        label: 'Invoice Lunas',
        value: formatCurrency(props.dashboardFinancial?.total_sudah_dibayar ?? 0),
        note: `${props.dashboardFinancial?.jumlah_sudah_dibayar ?? 0} lunas`,
        tone: 'text-emerald-600',
    },
    {
        label: 'Invoice Belum Lunas',
        value: formatCurrency(props.dashboardFinancial?.total_belum_dibayar ?? 0),
        note: `${props.dashboardFinancial?.jumlah_belum_dibayar ?? 0} belum`,
        tone: 'text-amber-600',
    },
    {
        label: 'Nota Toko',
        value: formatCurrency(props.dashboardNotaToko?.total_semua ?? 0),
        note: `${props.dashboardNotaToko?.jumlah_semua ?? 0} nota`,
        tone: 'text-cyan-700',
    },
])

const workflowCards = computed(() => [
    {
        label: 'Penawaran Draft',
        value: props.dashboardStatus?.penawaran?.draft ?? 0,
        badge: 'Draft',
        badgeClass: 'bg-slate-100 text-slate-600',
        note: 'Penawaran yang masih bisa diedit sebelum dikirim atau diajukan.',
        to: '/penawaran',
    },
    {
        label: 'Menunggu PO',
        value: props.dashboardStatus?.purchasing_order?.menunggu_upload ?? 0,
        badge: 'Proses',
        badgeClass: 'bg-amber-100 text-amber-700',
        note: 'Penawaran approved yang belum punya dokumen PO.',
        to: '/penawaran',
    },
    {
        label: 'Invoice Unpaid',
        value: props.dashboardStatus?.invoice?.belum_dibayar ?? 0,
        badge: 'Tagihan',
        badgeClass: 'bg-rose-100 text-rose-700',
        note: 'Invoice yang masih menunggu pembayaran pelanggan.',
        to: '/invoice',
    },
    {
        label: 'Faktur Pending',
        value: props.dashboardStatus?.faktur_pajak?.menunggu_upload ?? 0,
        badge: 'Pajak',
        badgeClass: 'bg-cyan-100 text-cyan-700',
        note: 'Invoice yang belum memiliki faktur pajak tersambung.',
        to: '/invoice',
    },
])

const recentTransactions = computed(() => {
    return (props.dashboardTransactions ?? []).slice(0, 6).map((row) => {
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
            detailLink: `/penawaran/${row.penawaran.id}`,
            pdfLink: `/penawaran/${row.penawaran.id}/pdf?download=1`,
        }
    })
})

const attentionItems = computed(() => [
    {
        label: 'Penawaran submitted',
        value: props.dashboardStatus?.penawaran?.submitted ?? 0,
        note: 'Perlu ditinjau sebelum masuk ke tahap approval.',
        to: '/penawaran',
    },
    {
        label: 'Approved tanpa PO',
        value: props.dashboardStatus?.purchasing_order?.menunggu_upload ?? 0,
        note: 'Siap diproses ke dokumen Purchasing Order.',
        to: '/penawaran',
    },
    {
        label: 'Faktur pajak menunggu',
        value: props.dashboardStatus?.faktur_pajak?.menunggu_upload ?? 0,
        note: 'Belum ada faktur pajak yang tersambung ke invoice.',
        to: '/invoice',
    },
])
</script>
