<template>
  <form class="space-y-6" @submit.prevent="submit">
    <div>
      <p class="text-sm font-semibold uppercase tracking-[0.3em] text-cyan-700">Masuk</p>
      <h1 class="mt-3 text-3xl font-semibold text-slate-900">Login ke workspace</h1>
      <p class="mt-3 text-sm leading-6 text-slate-600">
        Gunakan akun PT ASKARYA yang sudah tersambung ke company. Session akan dipakai untuk mengakses API dan halaman frontend ini.
      </p>
    </div>

    <div v-if="errorMessage" class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
      {{ errorMessage }}
    </div>

    <label class="block">
      <span class="mb-2 block text-sm font-medium text-slate-700">Email</span>
      <input v-model="form.email" type="email" autocomplete="email" class="input" placeholder="nama@perusahaan.com" />
    </label>

    <label class="block">
      <span class="mb-2 block text-sm font-medium text-slate-700">Password</span>
      <input v-model="form.password" type="password" autocomplete="current-password" class="input" placeholder="********" />
    </label>

    <label class="flex items-center gap-3 text-sm text-slate-600">
      <input v-model="form.remember" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" />
      Ingat saya
    </label>

    <button type="submit" class="button-primary w-full" :disabled="pending">
      {{ pending ? 'Memproses...' : 'Masuk' }}
    </button>
  </form>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'auth',
  middleware: 'guest',
})

const route = useRoute()
const apiFetch = useApi()
const { refresh } = useSession()

const pending = ref(false)
const errorMessage = ref('')
const form = reactive({
  email: '',
  password: '',
  remember: true,
})

async function submit() {
  pending.value = true
  errorMessage.value = ''

  try {
    await apiFetch('/sanctum/csrf-cookie')
    await apiFetch('/login', {
      method: 'POST',
      headers: {
        Accept: 'application/json',
      },
      body: form,
    })

    await refresh()

    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : '/dashboard'
    await navigateTo(redirect)
  } catch (error: any) {
    errorMessage.value = error?.data?.message ?? 'Login gagal. Periksa email dan password.'
  } finally {
    pending.value = false
  }
}
</script>
