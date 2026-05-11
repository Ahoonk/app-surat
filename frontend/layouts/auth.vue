<template>
  <div class="min-h-screen flex items-center justify-center p-6">
    <div class="grid w-full max-w-6xl gap-6 lg:grid-cols-[1.1fr_0.9fr]">
      <section class="glass subtle-grid rounded-[32px] p-8 text-white overflow-hidden relative">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-slate-900 to-cyan-900 opacity-95"></div>
        <div class="absolute -right-16 -top-20 h-64 w-64 rounded-full bg-cyan-500/25 blur-3xl"></div>
        <div class="absolute -bottom-12 left-10 h-56 w-56 rounded-full bg-blue-500/20 blur-3xl"></div>

        <div class="relative z-10 flex h-full flex-col justify-between gap-12">
          <div>
            <p class="text-sm uppercase tracking-[0.35em] text-cyan-200/80">Surat App</p>
            <h1 class="mt-4 max-w-xl text-4xl font-semibold leading-tight sm:text-5xl">
              Dashboard dokumen bisnis yang lebih tenang, cepat, dan terstruktur.
            </h1>
            <p class="mt-4 max-w-xl text-sm leading-7 text-slate-200/85">
              Login ke backend Laravel, lalu lanjutkan pengelolaan penawaran, invoice, PO, dan dokumen turunan dari satu alur kerja.
            </p>
          </div>

          <div class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-2xl border border-white/10 bg-white/8 p-4">
              <p class="text-xs uppercase tracking-[0.25em] text-cyan-100/70">Auth</p>
              <p class="mt-2 text-lg font-semibold">Sanctum</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/8 p-4">
              <p class="text-xs uppercase tracking-[0.25em] text-cyan-100/70">Frontend</p>
              <p class="mt-2 text-lg font-semibold">Nuxt 3</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/8 p-4">
              <p class="text-xs uppercase tracking-[0.25em] text-cyan-100/70">Backend</p>
              <p class="mt-2 text-lg font-semibold">Laravel API</p>
            </div>
          </div>
        </div>
      </section>

      <section class="panel flex items-center justify-center p-8 sm:p-10">
        <div class="w-full max-w-md">
          <slot />
        </div>
      </section>
    </div>
  </div>
</template>
