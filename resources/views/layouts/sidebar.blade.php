@php
    $appName = config('app.name', 'PT ASKARYA');
    $user = auth()->user();
    $brandLogo = asset('storage/logos/aldera.png');

    $menuClass = function (string ...$patterns) {
        $isActive = request()->routeIs(...$patterns);

        return $isActive
            ? 'flex items-center justify-between gap-3 rounded-2xl border border-cyan-400/30 bg-white/10 px-4 py-3 text-white shadow-lg shadow-cyan-950/20'
            : 'flex items-center justify-between gap-3 rounded-2xl px-4 py-3 text-slate-200 transition hover:bg-white/10 hover:text-white';
    };
@endphp

<div class="flex h-full min-h-screen flex-col bg-gradient-to-b from-slate-950 via-slate-900 to-slate-800 text-slate-100 shadow-2xl">
    <div class="border-b border-white/10 p-6">
        <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-white shadow-lg">
                <img src="{{ $brandLogo }}" class="h-10 w-10 object-contain" alt="Logo PT ASKARYA">
            </div>

            <div class="min-w-0">
                <p class="text-[11px] uppercase tracking-[0.35em] text-cyan-200/70">Admin Workspace</p>
                <h2 class="mt-1 truncate text-lg font-semibold text-white">{{ $appName }}</h2>
                <p class="truncate text-sm text-slate-300">{{ $user?->name ?? 'Guest' }}</p>
                <p class="mt-1 text-xs uppercase tracking-[0.25em] text-cyan-200/60">{{ $user?->role ?? 'admin' }}</p>
            </div>
        </div>
    </div>

    <nav class="flex-1 space-y-8 overflow-y-auto px-4 py-6 text-sm">
        <div>
            <p class="mb-3 px-2 text-[11px] uppercase tracking-[0.35em] text-slate-400">Overview</p>
            <a href="{{ route('dashboard') }}" class="{{ $menuClass('dashboard') }}">
                <span>Dashboard</span>
                <span class="text-xs text-slate-400">Ringkasan</span>
            </a>
        </div>

        <div>
            <p class="mb-3 px-2 text-[11px] uppercase tracking-[0.35em] text-slate-400">Transaksi</p>
            <div class="space-y-2">
                <a href="{{ route('penawaran.index') }}" class="{{ $menuClass('penawaran.*') }}">
                    <span>Surat Penawaran</span>
                    <span class="text-xs text-slate-400">Proposal</span>
                </a>
                <a href="{{ route('purchasing-order.index') }}" class="{{ $menuClass('purchasing-order.*') }}">
                    <span>Purchasing Order</span>
                    <span class="text-xs text-slate-400">PO</span>
                </a>
                <a href="{{ route('invoice.index') }}" class="{{ $menuClass('invoice.*') }}">
                    <span>Invoice</span>
                    <span class="text-xs text-slate-400">Penagihan</span>
                </a>
                <a href="{{ route('faktur-pajak.index') }}" class="{{ $menuClass('faktur-pajak.*') }}">
                    <span>Faktur Pajak</span>
                    <span class="text-xs text-slate-400">Pajak</span>
                </a>
                <a href="{{ route('surat-jalan.index') }}" class="{{ $menuClass('surat-jalan.*') }}">
                    <span>Surat Jalan</span>
                    <span class="text-xs text-slate-400">Delivery</span>
                </a>
                <a href="{{ route('berita-acara.index') }}" class="{{ $menuClass('berita-acara.*') }}">
                    <span>Berita Acara</span>
                    <span class="text-xs text-slate-400">BA</span>
                </a>
                <a href="{{ route('nota-toko.index') }}" class="{{ $menuClass('nota-toko.*') }}">
                    <span>Nota Toko</span>
                    <span class="text-xs text-slate-400">Retail</span>
                </a>
                <a href="{{ route('simulasi-pembiayaan.index') }}" class="{{ $menuClass('simulasi-pembiayaan.*') }}">
                    <span>Simulasi Pembiayaan</span>
                    <span class="text-xs text-slate-400">Calculator</span>
                </a>
            </div>
        </div>

        <div>
            <p class="mb-3 px-2 text-[11px] uppercase tracking-[0.35em] text-slate-400">Master Data</p>
            <div class="space-y-2">
                <a href="{{ route('customers.index') }}" class="{{ $menuClass('customers.*') }}">
                    <span>Customer</span>
                    <span class="text-xs text-slate-400">Master</span>
                </a>
                <a href="{{ route('mitra.index') }}" class="{{ $menuClass('mitra.*') }}">
                    <span>Mitra</span>
                    <span class="text-xs text-slate-400">Partner</span>
                </a>
                @if ($user?->isSuperAdmin())
                    <a href="{{ route('users.index') }}" class="{{ $menuClass('users.*') }}">
                        <span>Manajemen User</span>
                        <span class="text-xs text-slate-400">Akses</span>
                    </a>
                @endif
            </div>
        </div>
    </nav>

    <div class="border-t border-white/10 p-5">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="button-ghost w-full justify-center border-white/10 bg-white/5 text-white hover:bg-white/10">
                Logout
            </button>
        </form>
    </div>
</div>
