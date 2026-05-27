<template>
  <div class="space-y-6">
    <section class="panel p-6">
      <div>
        <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Master data</p>
        <h2 class="section-title mt-2">Mitra</h2>
        <p class="mt-3 text-sm text-slate-600">Daftar mitra untuk nomor dokumen dan template yang berbeda per partner.</p>
      </div>
    </section>

    <section class="panel overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full min-w-[900px] border-collapse text-sm">
          <thead class="bg-slate-50 text-left text-slate-500">
            <tr>
              <th class="px-5 py-4 font-medium">Nama</th>
              <th class="px-5 py-4 font-medium">Email</th>
              <th class="px-5 py-4 font-medium">Alamat</th>
              <th class="px-5 py-4 font-medium">Nomor Penawaran</th>
              <th class="px-5 py-4 font-medium">Nomor Invoice</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in rows" :key="item.id" class="border-t border-slate-200">
              <td class="px-5 py-4 font-medium text-slate-900">{{ item.nama }}</td>
              <td class="px-5 py-4 text-slate-600">{{ item.email ?? '-' }}</td>
              <td class="px-5 py-4 text-slate-600">{{ item.alamat ?? '-' }}</td>
              <td class="px-5 py-4 text-slate-600">{{ item.nomor_penawaran ?? '-' }}</td>
              <td class="px-5 py-4 text-slate-600">{{ item.nomor_invoice ?? '-' }}</td>
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
const { data } = await useAsyncData('mitras', () => apiFetch<any>('/api/mitras'))
const rows = computed(() => data.value?.data ?? [])
</script>
