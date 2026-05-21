<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'PT ASKARYA') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-transparent text-slate-900">
        <div class="min-h-screen lg:grid lg:grid-cols-[288px_1fr]">
            <div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-slate-950/50 backdrop-blur-sm lg:hidden"></div>

            <aside id="app-sidebar" class="fixed inset-y-0 left-0 z-40 w-[288px] -translate-x-full transform transition-transform duration-200 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0">
                @include('layouts.sidebar')
            </aside>

            <div class="min-w-0">
                <header class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/85 backdrop-blur-xl lg:hidden">
                    <div class="flex items-center justify-between gap-3 px-4 py-3">
                        <button id="sidebar-open" type="button" class="button-ghost px-3 py-2 text-sm">
                            Menu
                        </button>
                        <div class="text-sm font-semibold text-slate-700">{{ config('app.name', 'PT ASKARYA') }}</div>
                    </div>
                </header>

                <main class="p-4 sm:p-6 lg:p-8">
                    @if (session('success'))
                        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            {{ session('error') }}
                        </div>
                    @endif

                    {{ $slot ?? '' }}
                    @yield('content')
                </main>
            </div>
        </div>

        <script>
            const sidebar = document.getElementById('app-sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const openButton = document.getElementById('sidebar-open');

            function openSidebar() {
                sidebar?.classList.remove('-translate-x-full');
                overlay?.classList.remove('hidden');
            }

            function closeSidebar() {
                if (window.matchMedia('(min-width: 1024px)').matches) {
                    overlay?.classList.add('hidden');
                    return;
                }

                sidebar?.classList.add('-translate-x-full');
                overlay?.classList.add('hidden');
            }

            openButton?.addEventListener('click', openSidebar);
            overlay?.addEventListener('click', closeSidebar);
            window.addEventListener('resize', closeSidebar);
        </script>
    </body>
</html>
