<template>
  <div class="space-y-6">
    <section class="panel overflow-hidden">
      <div class="grid gap-6 p-6 lg:grid-cols-[1.15fr_0.85fr] lg:p-8">
        <div class="space-y-5">
          <div>
            <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Company Profile</p>
            <h2 class="section-title mt-3">{{ settings?.hero_title ?? companyName }}</h2>
            <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-600">
              {{ settings?.hero_description ?? 'Profil perusahaan yang dirangkai ulang dari data lama dan sekarang ikut hidup di frontend baru.' }}
            </p>
          </div>

          <div class="flex flex-wrap gap-3">
            <span class="rounded-full bg-cyan-50 px-4 py-2 text-sm font-medium text-cyan-800">
              {{ settings?.tagline ?? 'Digital workspace' }}
            </span>
            <span class="rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700">
              {{ company?.address ?? settings?.company_address ?? '-' }}
            </span>
          </div>
        </div>

        <div class="rounded-[26px] bg-gradient-to-br from-slate-950 via-slate-900 to-cyan-900 p-6 text-white shadow-xl">
          <p class="text-xs uppercase tracking-[0.35em] text-cyan-200/70">Identity</p>
          <h3 class="mt-3 text-2xl font-semibold">{{ companyName }}</h3>
          <div class="mt-5 space-y-3 text-sm leading-6 text-slate-200/85">
            <p>{{ settings?.profile_title ?? 'Partner teknologi yang memahami kebutuhan bisnis anda' }}</p>
            <p>{{ settings?.profile_description ?? '-' }}</p>
          </div>

          <div class="mt-6 grid grid-cols-2 gap-3 text-sm">
            <div class="rounded-2xl bg-white/10 p-4">
              <p class="text-xs uppercase tracking-[0.25em] text-cyan-100/70">Email</p>
              <p class="mt-2 font-medium">{{ settings?.contact_email ?? '-' }}</p>
            </div>
            <div class="rounded-2xl bg-white/10 p-4">
              <p class="text-xs uppercase tracking-[0.25em] text-cyan-100/70">Phone</p>
              <p class="mt-2 font-medium">{{ settings?.contact_phone ?? '-' }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="grid gap-4 lg:grid-cols-3">
      <article class="panel p-6">
        <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Visi</p>
        <h3 class="mt-3 text-xl font-semibold text-slate-900">{{ settings?.vision_title ?? 'Visi' }}</h3>
        <p class="mt-3 text-sm leading-7 text-slate-600">
          {{ settings?.vision_description ?? '-' }}
        </p>
      </article>

      <article class="panel p-6 lg:col-span-2">
        <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Misi</p>
        <h3 class="mt-3 text-xl font-semibold text-slate-900">Langkah yang kami pegang</h3>
        <ul class="mt-4 grid gap-3 md:grid-cols-2">
          <li
            v-for="(item, index) in missionItems"
            :key="index"
            class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-700"
          >
            {{ item }}
          </li>
        </ul>
      </article>
    </section>

    <section class="grid gap-4 lg:grid-cols-2">
      <article class="panel p-6">
        <p class="text-sm uppercase tracking-[0.3em] text-slate-500">About</p>
        <h3 class="mt-3 text-xl font-semibold text-slate-900">{{ settings?.about_title ?? 'About Us' }}</h3>
        <p class="mt-3 text-sm leading-7 text-slate-600">
          {{ settings?.about_description ?? '-' }}
        </p>
      </article>

      <article class="panel p-6">
        <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Informasi</p>
        <h3 class="mt-3 text-xl font-semibold text-slate-900">Kontak dan tautan</h3>
        <div class="mt-4 space-y-3 text-sm leading-6 text-slate-600">
          <p><span class="font-medium text-slate-900">Slug About:</span> {{ settings?.about_slug ?? '-' }}</p>
          <p><span class="font-medium text-slate-900">Slug Clients:</span> {{ settings?.clients_slug ?? '-' }}</p>
          <p><span class="font-medium text-slate-900">Slug Products:</span> {{ settings?.products_slug ?? '-' }}</p>
          <p><span class="font-medium text-slate-900">WhatsApp:</span> {{ settings?.contact_whatsapp ?? '-' }}</p>
        </div>
      </article>
    </section>

    <section class="grid gap-4 md:grid-cols-2">
      <article class="panel p-6">
        <div class="flex items-center justify-between gap-4">
          <div>
            <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Clients</p>
            <h3 class="mt-3 text-xl font-semibold text-slate-900">Portofolio yang tersimpan</h3>
          </div>
          <NuxtLink to="/clients" class="button-ghost px-3 py-2 text-sm">Lihat semua</NuxtLink>
        </div>
        <p class="mt-4 text-sm leading-7 text-slate-600">
          Data client lama sudah kita taruh di database baru dan bisa ditampilkan kembali di frontend ini.
        </p>
      </article>

      <article class="panel p-6">
        <div class="flex items-center justify-between gap-4">
          <div>
            <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Products</p>
            <h3 class="mt-3 text-xl font-semibold text-slate-900">Layanan utama</h3>
          </div>
          <NuxtLink to="/products" class="button-ghost px-3 py-2 text-sm">Lihat semua</NuxtLink>
        </div>
        <p class="mt-4 text-sm leading-7 text-slate-600">
          Produk dan layanan lama juga bisa dihidupkan kembali sebagai katalog perusahaan.
        </p>
      </article>
    </section>
  </div>
</template>

<script setup lang="ts">
import type { CompanySettings } from '~/types/api'

definePageMeta({
  middleware: 'auth',
})

const { session, ensure } = useSession()
await useAsyncData('company-profile', () => ensure())

const company = computed(() => session.value?.company ?? null)
const settings = computed<CompanySettings | null>(() => company.value?.settings ?? null)
const companyName = computed(() => settings.value?.company_name ?? company.value?.name ?? 'Company Profile')
const missionItems = computed(() => settings.value?.mission_items ?? [])
</script>
