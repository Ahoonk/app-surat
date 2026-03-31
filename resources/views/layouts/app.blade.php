<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-100">
        <div class="min-h-screen lg:flex lg:items-stretch">
            <div id="sidebar-overlay" class="fixed inset-0 z-30 bg-black/40 hidden lg:hidden"></div>

            <aside id="app-sidebar" class="fixed inset-y-0 left-0 z-40 -translate-x-full transform transition-transform duration-200 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0">
                @include('layouts.sidebar')
            </aside>

            <div class="flex-1 min-w-0">
                <div class="lg:hidden sticky top-0 z-20 flex items-center justify-between bg-white border-b px-4 py-3">
                    <button id="sidebar-open" type="button" class="rounded-lg border px-3 py-2 text-sm">
                        Menu
                    </button>
                    <div class="text-sm font-semibold text-gray-700">Surat App</div>
                </div>

                <main class="p-4 sm:p-6 lg:p-8">
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
