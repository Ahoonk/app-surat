<template>
    <div class="min-h-screen grid lg:grid-cols-[290px_1fr]">
        <aside class="hidden lg:flex flex-col bg-slate-950 text-slate-100">
            <div class="p-6 border-b border-white/10">
                <p class="text-xs uppercase tracking-[0.4em] text-cyan-200/80">{{ appName }}</p>
                <h2 class="mt-2 text-2xl font-semibold">Workspace</h2>
                <p class="mt-2 text-sm text-slate-300">Dokumen bisnis, invoice, dan surat turunan dalam satu alur.</p>
            </div>

            <nav class="flex-1 p-4 space-y-2">
                <a v-for="link in links" :key="link.to" :href="link.to" class="nav-link">
                    <span>{{ link.label }}</span>
                    <span class="text-xs text-slate-400">{{ link.hint }}</span>
                </a>
            </nav>

            <div class="p-5 border-t border-white/10">
                <div class="rounded-2xl bg-white/5 p-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-cyan-200/70">Signed in</p>
                    <p class="mt-2 font-medium">{{ userName }}</p>
                    <p class="text-sm text-slate-300">{{ companyName }}</p>
                </div>
                <button class="button-ghost mt-4 w-full justify-center border-white/10 bg-white/5 text-white" @click="handleLogout">
                    Logout
                </button>
            </div>
        </aside>

        <main class="min-h-screen">
            <header class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/80 backdrop-blur-xl">
                <div class="flex items-center justify-between gap-4 px-5 py-4 lg:px-8">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-500">{{ appName }}</p>
                        <h1 class="text-lg font-semibold text-slate-900">{{ currentTitle }}</h1>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="hidden sm:block text-right">
                            <p class="text-sm font-medium text-slate-900">{{ userName }}</p>
                            <p class="text-xs text-slate-500">{{ userRole }}</p>
                        </div>
                        <div class="h-11 w-11 rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 text-white flex items-center justify-center font-semibold shadow-lg">
                            {{ initials }}
                        </div>
                    </div>
                </div>
            </header>

            <div class="page-shell">
                <slot />
            </div>
        </main>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const page = usePage()
const appName = computed(() => page.props.appName || 'PT ASKARYA')
const user = computed(() => page.props.auth?.user ?? null)
const userName = computed(() => user.value?.name ?? 'Guest')
const userRole = computed(() => user.value?.role ?? 'user')
const companyName = computed(() => page.props.company?.name ?? 'Company')

const links = [
    { label: 'Dashboard', to: '/dashboard', hint: 'Ringkasan' },
    { label: 'Surat Penawaran', to: '/penawaran', hint: 'Proposal' },
    { label: 'Purchasing Order', to: '/purchasing-order', hint: 'PO' },
    { label: 'Invoice', to: '/invoice', hint: 'Penagihan' },
    { label: 'Nota Toko', to: '/nota-toko', hint: 'Retail' },
    { label: 'Surat Jalan', to: '/surat-jalan', hint: 'Delivery' },
    { label: 'Berita Acara', to: '/berita-acara', hint: 'BA' },
    { label: 'Customer', to: '/customers', hint: 'Master' },
    { label: 'Mitra', to: '/mitra', hint: 'Partner' },
]

const currentTitle = computed(() => {
    const path = String(page.url ?? '')

    if (path === '/dashboard') return 'Dashboard'
    if (path.startsWith('/penawaran')) return 'Surat Penawaran'
    if (path.startsWith('/purchasing-order')) return 'Purchasing Order'
    if (path.startsWith('/invoice')) return 'Invoice'
    if (path.startsWith('/nota-toko')) return 'Nota Toko'
    if (path.startsWith('/surat-jalan')) return 'Surat Jalan'
    if (path.startsWith('/berita-acara')) return 'Berita Acara'
    if (path.startsWith('/customers')) return 'Customer'
    if (path.startsWith('/mitra')) return 'Mitra'
    return 'PT ASKARYA'
})

const initials = computed(() => {
    const name = user.value?.name ?? 'SA'
    return name
        .split(' ')
        .map((part) => part.slice(0, 1))
        .join('')
        .slice(0, 2)
        .toUpperCase()
})

function handleLogout() {
    router.post('/logout', {}, {
        onFinish: () => {
            window.location.href = '/login'
        },
    })
}
</script>

<style scoped>
.nav-link {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    border-radius: 18px;
    padding: 0.95rem 1rem;
    transition: background 0.2s ease, transform 0.2s ease;
}

.nav-link:hover {
    background: rgba(255, 255, 255, 0.06);
    transform: translateX(4px);
}
</style>
