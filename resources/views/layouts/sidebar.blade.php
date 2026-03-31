<div class="w-64 bg-gradient-to-b from-blue-950 via-blue-900 to-blue-800 
            text-blue-100 h-full min-h-screen lg:min-h-0 flex flex-col shadow-2xl">
@php
    $menuClass = function (string ...$patterns) {
        $isActive = request()->routeIs(...$patterns);
        return $isActive
            ? 'flex items-center px-4 py-2 rounded-lg bg-white/15 text-white font-medium border-l-4 border-blue-400'
            : 'flex items-center px-4 py-2 rounded-lg hover:bg-white/10 transition-all duration-300 hover:translate-x-1';
    };
@endphp

   <!-- COMPANY HEADER -->
<div class="p-6 border-b border-blue-700/40">

    <div class="flex items-center gap-4">

        <!-- LOGO BULAT -->
       <div class="w-16 h-16 flex items-center justify-center 
            bg-white rounded-full shadow-md shrink-0">
    <img src="{{ asset('storage/logos/aldera.png') }}"
         class="h-11 w-11 object-contain"
         alt="Logo">
</div>

        <!-- TEXT -->
        <div class="leading-tight">
            <div class="text-base font-semibold text-white">
                PT Aldera Saddatech Karya
            </div>

            <div class="text-sm text-blue-200 mt-1">
                {{ auth()->user()->name }}
            </div>
            <div class="text-xs text-blue-300/80 mt-1 capitalize">
                {{ auth()->user()->role ?? 'admin' }}
            </div>
        </div>

    </div>

</div>

    <!-- MENU -->
    <div class="flex-1 overflow-y-auto px-4 py-8 space-y-8 text-sm">

        <!-- DASHBOARD -->
        <div>
            <a href="{{ route('dashboard') }}"
               class="{{ $menuClass('dashboard') }}">
                Dashboard
            </a>
        </div>

        <!-- TRANSAKSI -->
        <div>
            <div class="text-xs uppercase text-blue-300/70 mb-3 tracking-widest">
                Transaksi
            </div>

            <div class="space-y-2">

                <a href="{{ route('penawaran.index') }}"
                   class="{{ $menuClass('penawaran.*') }}">
                    Surat Penawaran
                </a>

                <a href="{{ route('purchasing-order.index') }}"
                   class="{{ $menuClass('purchasing-order.*') }}">
                    Purchasing Order
                </a>

                <a href="{{ route('invoice.index') }}"
                   class="{{ $menuClass('invoice.*') }}">
                    Invoice
                </a>

                <a href="{{ route('faktur-pajak.index') }}"
                   class="{{ $menuClass('faktur-pajak.*') }}">
                    Faktur Pajak
                </a>

                <a href="{{ route('surat-jalan.index') }}"
                   class="{{ $menuClass('surat-jalan.*') }}">
                    Surat Jalan
                </a>

                <a href="{{ route('berita-acara.index') }}"
                   class="{{ $menuClass('berita-acara.*') }}">
                    Berita Acara
                </a>

                <a href="{{ route('nota-toko.index') }}"
                   class="{{ $menuClass('nota-toko.*') }}">
                    Nota Toko
                </a>

                <a href="{{ route('simulasi-pembiayaan.index') }}"
                   class="{{ $menuClass('simulasi-pembiayaan.*') }}">
                    Simulasi Pembiayaan
                </a>

            </div>
        </div>

        <!-- MASTER -->
        <div>
            <div class="text-xs uppercase text-blue-300/70 mb-3 tracking-widest">
                Master Data
            </div>

            <div class="space-y-2">

                <a href="{{ route('customers.index') }}"
                   class="{{ $menuClass('customers.*') }}">
                    Customer
                </a>

                @if(auth()->user()?->isSuperAdmin())
                    <a href="{{ route('users.index') }}"
                       class="{{ $menuClass('users.*') }}">
                        Manajemen User
                    </a>
                @endif

            </div>
        </div>

    </div>

    <!-- LOGOUT -->
    <div class="p-5 border-t border-blue-700/40">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="w-full text-left px-4 py-2 rounded-lg
                           hover:bg-red-500 hover:text-white
                           transition-all duration-300">
                Logout
            </button>
        </form>
    </div>

</div>
