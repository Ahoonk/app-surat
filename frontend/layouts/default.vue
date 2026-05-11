<template>
  <div class="min-h-screen grid lg:grid-cols-[290px_1fr]">
    <aside class="hidden lg:flex flex-col bg-slate-950 text-slate-100">
      <div class="p-6 border-b border-white/10">
        <p class="text-xs uppercase tracking-[0.4em] text-cyan-200/80">Surat App</p>
        <h2 class="mt-2 text-2xl font-semibold">Workspace</h2>
        <p class="mt-2 text-sm text-slate-300">Frontend terpisah untuk alur dokumen yang sudah berjalan.</p>
      </div>

      <nav class="flex-1 p-4 space-y-2">
        <NuxtLink v-for="link in links" :key="link.to" :to="link.to" class="nav-link">
          <span>{{ link.label }}</span>
          <span class="text-xs text-slate-400">{{ link.hint }}</span>
        </NuxtLink>
      </nav>

      <div class="p-5 border-t border-white/10">
        <div class="rounded-2xl bg-white/5 p-4">
          <p class="text-xs uppercase tracking-[0.3em] text-cyan-200/70">Signed in</p>
          <p class="mt-2 font-medium">{{ session?.user?.name ?? 'Memuat...' }}</p>
          <p class="text-sm text-slate-300">{{ session?.company?.name ?? 'Company' }}</p>
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
            <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Surat App</p>
            <h1 class="text-lg font-semibold text-slate-900">{{ currentTitle }}</h1>
          </div>

          <div class="flex items-center gap-3">
            <div class="hidden sm:block text-right">
              <p class="text-sm font-medium text-slate-900">{{ session?.user?.name ?? 'Guest' }}</p>
              <p class="text-xs text-slate-500">{{ session?.user?.role ?? 'user' }}</p>
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

<script setup lang="ts">
const route = useRoute()
const { session, logout } = useSession()

const links = [
  { label: 'Dashboard', to: '/dashboard', hint: 'Ringkasan' },
  { label: 'Company Profile', to: '/about-us', hint: 'Profil' },
  { label: 'Surat Penawaran', to: '/penawaran', hint: 'Proposal' },
  { label: 'Invoice', to: '/invoice', hint: 'Penagihan' },
  { label: 'Surat Jalan', to: '/surat-jalan', hint: 'Delivery' },
  { label: 'Berita Acara', to: '/berita-acara', hint: 'BA' },
  { label: 'Customer', to: '/customers', hint: 'Master' },
  { label: 'Mitra', to: '/mitras', hint: 'Partner' },
]

const currentTitle = computed(() => {
  if (route.path === '/dashboard') return 'Dashboard'
  if (route.path.startsWith('/about-us')) return 'Company Profile'
  if (route.path.startsWith('/penawaran')) return 'Surat Penawaran'
  if (route.path.startsWith('/invoice')) return 'Invoice'
  if (route.path.startsWith('/surat-jalan')) return 'Surat Jalan'
  if (route.path.startsWith('/berita-acara')) return 'Berita Acara'
  if (route.path.startsWith('/customers')) return 'Customer'
  if (route.path.startsWith('/mitras')) return 'Mitra'
  return 'Surat App'
})

const initials = computed(() => {
  const name = session.value?.user?.name ?? 'SA'
  return name
    .split(' ')
    .map((part) => part.slice(0, 1))
    .join('')
    .slice(0, 2)
    .toUpperCase()
})

async function handleLogout() {
  await logout()
  await navigateTo('/login')
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

.nav-link.router-link-active {
  background: rgba(255, 255, 255, 0.08);
  transform: translateX(4px);
}

.nav-link:hover {
  background: rgba(255, 255, 255, 0.06);
}
</style>
