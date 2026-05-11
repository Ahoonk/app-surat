<template>
  <div class="space-y-6">
    <section class="panel p-6">
      <div>
        <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Master data</p>
        <h2 class="section-title mt-2">Customer</h2>
        <p class="mt-3 text-sm text-slate-600">Daftar customer yang nanti dapat dihubungkan ke form transaksi frontend baru.</p>
      </div>
    </section>

    <section class="panel overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full min-w-[800px] border-collapse text-sm">
          <thead class="bg-slate-50 text-left text-slate-500">
            <tr>
              <th class="px-5 py-4 font-medium">Nama</th>
              <th class="px-5 py-4 font-medium">Alamat</th>
              <th class="px-5 py-4 font-medium">HP</th>
              <th class="px-5 py-4 font-medium">Email</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in rows" :key="item.id" class="border-t border-slate-200">
              <td class="px-5 py-4 font-medium text-slate-900">{{ item.nama }}</td>
              <td class="px-5 py-4 text-slate-600">{{ item.alamat }}</td>
              <td class="px-5 py-4 text-slate-600">{{ item.no_hp }}</td>
              <td class="px-5 py-4 text-slate-600">{{ item.email }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth',
})

const apiFetch = useApi()
const { data } = await useAsyncData('customers', () => apiFetch<any>('/api/customers'))
const rows = computed(() => data.value?.data ?? [])
</script>
