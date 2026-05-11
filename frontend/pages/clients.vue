<template>
  <div class="space-y-6">
    <section class="panel p-6">
      <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
          <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Recovered Data</p>
          <h2 class="section-title mt-2">Clients</h2>
          <p class="mt-3 text-sm text-slate-600">
            Daftar client dari backup lama yang sekarang tampil lagi di workspace baru.
          </p>
        </div>
        <div class="text-sm text-slate-500">
          {{ clients.length }} client
        </div>
      </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      <article v-for="client in clients" :key="client.id" class="panel p-6">
        <div class="flex items-start justify-between gap-4">
          <div>
            <p class="text-xs uppercase tracking-[0.28em] text-cyan-700">Client</p>
            <h3 class="mt-2 text-lg font-semibold text-slate-900">{{ client.name }}</h3>
          </div>
          <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
            #{{ client.sort_order }}
          </span>
        </div>

        <p class="mt-4 text-sm font-medium text-slate-700">{{ client.sector ?? '-' }}</p>
        <p class="mt-3 text-sm leading-7 text-slate-600">{{ client.description ?? '-' }}</p>

        <div class="mt-5 rounded-2xl bg-slate-50 p-4 text-xs text-slate-500">
          {{ client.image_path ?? 'tanpa gambar' }}
        </div>
      </article>
    </section>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth',
})

const { session, ensure } = useSession()
await useAsyncData('clients-page', () => ensure())

const clients = computed(() => session.value?.siteProfile?.clients ?? [])
</script>
