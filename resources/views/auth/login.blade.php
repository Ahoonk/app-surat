<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistem Surat</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-900">
    <div class="relative min-h-screen overflow-hidden">
        <div
            class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(14,116,144,0.35),_transparent_28%),radial-gradient(circle_at_bottom_right,_rgba(59,130,246,0.24),_transparent_35%),linear-gradient(180deg,_#0f172a_0%,_#020617_100%)]">
        </div>
        <div class="absolute inset-0 bg-slate-950/65"></div>

        <div class="relative z-10 min-h-screen flex flex-col">
            <header class="px-4 pt-8 text-center sm:px-6 sm:pt-10 lg:pt-12">
                <p class="text-xs font-semibold uppercase tracking-[0.4em] text-cyan-200/80">
                    Sistem Administrasi Surat
                </p>
                <h1 class="mt-4 text-3xl font-black uppercase tracking-wide text-white sm:text-4xl lg:text-5xl"
                    style="font-family: Georgia, serif;">
                    Aplikasi Surat Elektronik
                </h1>
            </header>

            <main class="flex-1 flex items-center justify-center px-4 py-8 sm:px-6 sm:py-10">
                <div class="w-full max-w-5xl overflow-hidden rounded-[28px] border border-white/15 bg-white/95 shadow-[0_30px_70px_-20px_rgba(0,0,0,0.7)] backdrop-blur-md lg:grid lg:grid-cols-[1.05fr_0.95fr]">
                    <section class="p-6 sm:p-8 lg:p-10 xl:p-12">
                        <div class="mx-auto flex max-w-md flex-col justify-center">
                            <div class="mb-6 text-center">
                                <img src="{{ asset('storage/logos/aldera.png') }}"
                                     class="mx-auto h-16 w-auto object-contain sm:h-20"
                                     alt="Logo">
                                <h2 class="mt-5 text-2xl font-semibold text-slate-900 sm:text-3xl">
                                    Aldera Saddatech Karya
                                </h2>
                                <p class="mt-2 text-sm text-slate-500">
                                    Masuk untuk mengelola surat, invoice, dan dokumen transaksi.
                                </p>
                            </div>

                            @if ($errors->any())
                                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                                    <ul class="list-disc space-y-1 pl-5">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if (session('status'))
                                <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
                                    {{ session('status') }}
                                </div>
                            @endif

                            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                                @csrf

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                                    <input type="email"
                                           name="email"
                                           value="{{ old('email') }}"
                                           autocomplete="email"
                                           required
                                           class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-base text-slate-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/15">
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Password</label>
                                    <input type="password"
                                           name="password"
                                           autocomplete="current-password"
                                           required
                                           class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-base text-slate-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/15">
                                </div>

                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                                        <input type="checkbox"
                                               name="remember"
                                               class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                        <span>Ingat saya</span>
                                    </label>

                                    @if (Route::has('password.request'))
                                        <a href="{{ route('password.request') }}" class="text-sm font-medium text-blue-700 hover:text-blue-800">
                                            Lupa password?
                                        </a>
                                    @endif
                                </div>

                                <button type="submit"
                                        class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-base font-semibold text-white shadow-lg shadow-blue-600/25 transition hover:bg-blue-700">
                                    LOGIN
                                </button>

                                @if (Route::has('register'))
                                    <p class="text-center text-sm text-slate-500">
                                        Belum punya akun?
                                        <a href="{{ route('register') }}" class="font-semibold text-blue-700 hover:text-blue-800">
                                            Daftar di sini
                                        </a>
                                    </p>
                                @endif
                            </form>
                        </div>
                    </section>

                    <aside class="relative hidden min-h-[620px] lg:block">
                        <img src="{{ asset('storage/logos/gedung.png') }}"
                             class="absolute inset-0 h-full w-full object-cover"
                             alt="Gedung">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/55 via-transparent to-transparent"></div>
                        <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
                            <div class="max-w-sm rounded-2xl border border-white/15 bg-white/10 p-5 backdrop-blur-sm">
                                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-cyan-100/90">Aplikasi Internal</p>
                                <p class="mt-2 text-lg leading-relaxed text-white/95">
                                    Kelola penawaran, invoice, surat jalan, dan nota toko dalam satu alur kerja yang rapi.
                                </p>
                            </div>
                        </div>
                    </aside>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
