<template>
  <div class="space-y-6">
    <section class="panel p-6">
      <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
          <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Recovered Data</p>
          <h2 class="section-title mt-2">Products</h2>
          <p class="mt-3 text-sm text-slate-600">
            Katalog layanan lama yang bisa dipakai lagi tanpa kehilangan konteks bisnis sebelumnya.
          </p>
        </div>
        <div class="text-sm text-slate-500">
          {{ products.length }} layanan
        </div>
      </div>
    </section>

    <section class="grid gap-4 lg:grid-cols-2">
      <article v-for="product in products" :key="product.id" class="panel p-6">
        <div class="flex items-start justify-between gap-4">
          <div>
            <p class="text-xs uppercase tracking-[0.28em] text-cyan-700">Product</p>
            <h3 class="mt-2 text-lg font-semibold text-slate-900">{{ product.name }}</h3>
          </div>
          <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
            #{{ product.sort_order }}
          </span>
        </div>

        <p class="mt-4 text-sm leading-7 text-slate-600">{{ product.description ?? '-' }}</p>

        <div v-if="product.features?.length" class="mt-5 flex flex-wrap gap-2">
          <span
            v-for="feature in product.features"
            :key="feature"
            class="rounded-full bg-cyan-50 px-3 py-2 text-xs font-medium text-cyan-800"
          >
            {{ feature }}
          </span>
        </div>

        <div class="mt-5 rounded-2xl bg-slate-50 p-4 text-xs text-slate-500">
          {{ product.image_path ?? 'tanpa gambar' }}
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
await useAsyncData('products-page', () => ensure())

const products = computed(() => session.value?.siteProfile?.products ?? [])
</script>
