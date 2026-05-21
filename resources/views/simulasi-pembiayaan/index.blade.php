@extends('layouts.app')

@section('content')
@php
    $modalValue = (float) ($modal ?? old('modal', 0));
    $nilaiPengajuanValue = (float) ($nilaiPengajuan ?? old('nilai_pengajuan', 0));
    $biayaOperasionalValue = (float) ($biayaOperasional ?? old('biaya_operasional', 0));
    $jumlahBarangValue = (int) ($jumlahBarang ?? old('jumlah_barang', 1));
    $keuntunganValue = (float) ($keuntungan ?? 0);
    $pajakValue = (float) ($pajak ?? 0);
    $keuntunganBersihValue = (float) ($keuntunganBersih ?? 0);
@endphp

<div class="space-y-6">
    <section class="panel p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl">
                <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Tools</p>
                <h1 class="section-title mt-2">Simulasi Pembiayaan</h1>
                <p class="mt-3 text-sm text-slate-600">
                    Hitung keuntungan, pajak 11%, dan keuntungan bersih berdasarkan modal, nilai pengajuan, biaya operasional, dan jumlah barang.
                </p>
            </div>
            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Keuntungan</p>
                    <p id="summary-keuntungan" class="mt-1 text-lg font-semibold text-slate-900">Rp {{ number_format($keuntunganValue, 2, ',', '.') }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Pajak</p>
                    <p id="summary-pajak" class="mt-1 text-lg font-semibold text-slate-900">Rp {{ number_format($pajakValue, 2, ',', '.') }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Bersih</p>
                    <p id="summary-bersih" class="mt-1 text-lg font-semibold text-slate-900">Rp {{ number_format($keuntunganBersihValue, 2, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
        <div class="panel p-6">
            <form action="{{ route('simulasi-pembiayaan.calculate') }}" method="POST" class="space-y-5">
                @csrf

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-slate-700">Modal (Rp)</span>
                        <div class="flex items-center gap-3">
                            <span class="rounded-2xl bg-slate-100 px-4 py-3 text-sm text-slate-500">Rp</span>
                            <input id="modal" type="number" name="modal" min="0" step="0.01" value="{{ $modalValue }}" class="input">
                        </div>
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-slate-700">Nilai Pengajuan (Rp)</span>
                        <div class="flex items-center gap-3">
                            <span class="rounded-2xl bg-slate-100 px-4 py-3 text-sm text-slate-500">Rp</span>
                            <input id="nilai-pengajuan" type="number" name="nilai_pengajuan" min="0" step="0.01" value="{{ $nilaiPengajuanValue }}" class="input">
                        </div>
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-slate-700">Biaya Operasional (Rp)</span>
                        <div class="flex items-center gap-3">
                            <span class="rounded-2xl bg-slate-100 px-4 py-3 text-sm text-slate-500">Rp</span>
                            <input id="biaya-operasional" type="number" name="biaya_operasional" min="0" step="0.01" value="{{ $biayaOperasionalValue }}" class="input">
                        </div>
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-slate-700">Jumlah Barang</span>
                        <input id="jumlah-barang" type="number" name="jumlah_barang" min="1" step="1" value="{{ $jumlahBarangValue }}" class="input">
                    </label>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="button-primary">Hitung &amp; Simpan Hasil</button>
                    <button type="button" id="reset-button" class="button-ghost">Reset ke 0</button>
                </div>
            </form>
        </div>

        <div class="space-y-6">
            <section class="panel p-6">
                <h2 class="text-lg font-semibold text-slate-900">Hasil Perhitungan</h2>
                <p class="mt-1 text-sm text-slate-500">Nilai di bawah ini akan berubah real-time saat input diubah.</p>

                <div class="mt-5 grid gap-4">
                    <div class="rounded-3xl bg-gradient-to-br from-slate-950 to-cyan-900 p-5 text-white">
                        <p class="text-xs uppercase tracking-[0.3em] text-cyan-200/70">Keuntungan</p>
                        <p id="keuntungan" class="mt-3 text-3xl font-semibold">Rp {{ number_format($keuntunganValue, 2, ',', '.') }}</p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-3xl bg-cyan-50 p-5">
                            <p class="text-xs uppercase tracking-[0.25em] text-cyan-700/60">Pajak 11%</p>
                            <p id="pajak" class="mt-2 text-2xl font-semibold text-cyan-950">Rp {{ number_format($pajakValue, 2, ',', '.') }}</p>
                        </div>
                        <div class="rounded-3xl bg-emerald-50 p-5">
                            <p class="text-xs uppercase tracking-[0.25em] text-emerald-700/60">Keuntungan Bersih</p>
                            <p id="keuntungan-bersih" class="mt-2 text-2xl font-semibold text-emerald-950">Rp {{ number_format($keuntunganBersihValue, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="panel p-6">
                <h3 class="text-lg font-semibold text-slate-900">Catatan Perhitungan</h3>
                <ul class="mt-4 space-y-3 text-sm text-slate-600">
                    <li class="rounded-2xl bg-slate-50 px-4 py-3">Total modal = modal x jumlah barang</li>
                    <li class="rounded-2xl bg-slate-50 px-4 py-3">Keuntungan = total pengajuan - total modal</li>
                    <li class="rounded-2xl bg-slate-50 px-4 py-3">Pajak = 11% dari keuntungan jika hasilnya positif</li>
                    <li class="rounded-2xl bg-slate-50 px-4 py-3">Keuntungan bersih = keuntungan - pajak - biaya operasional</li>
                </ul>
            </section>
        </div>
    </section>
</div>

<script>
    const modalInput = document.getElementById('modal');
    const nilaiPengajuanInput = document.getElementById('nilai-pengajuan');
    const biayaOperasionalInput = document.getElementById('biaya-operasional');
    const jumlahBarangInput = document.getElementById('jumlah-barang');
    const keuntunganOutput = document.getElementById('keuntungan');
    const pajakOutput = document.getElementById('pajak');
    const keuntunganBersihOutput = document.getElementById('keuntungan-bersih');
    const summaryKeuntungan = document.getElementById('summary-keuntungan');
    const summaryPajak = document.getElementById('summary-pajak');
    const summaryBersih = document.getElementById('summary-bersih');
    const resetButton = document.getElementById('reset-button');

    const formatRupiah = (value) => new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value || 0);

    const calculate = () => {
        const modal = parseFloat(modalInput?.value || 0);
        const nilaiPengajuan = parseFloat(nilaiPengajuanInput?.value || 0);
        const biayaOperasional = parseFloat(biayaOperasionalInput?.value || 0);
        const jumlahBarang = parseInt(jumlahBarangInput?.value || 1, 10);

        const totalModal = modal * jumlahBarang;
        const totalPengajuan = nilaiPengajuan * jumlahBarang;
        const keuntungan = totalPengajuan - totalModal;
        const pajak = keuntungan > 0 ? keuntungan * 0.11 : 0;
        const keuntunganBersih = keuntungan - pajak - biayaOperasional;

        const keuntunganText = formatRupiah(keuntungan);
        const pajakText = formatRupiah(pajak);
        const bersihText = formatRupiah(keuntunganBersih);

        if (keuntunganOutput) keuntunganOutput.textContent = keuntunganText;
        if (pajakOutput) pajakOutput.textContent = pajakText;
        if (keuntunganBersihOutput) keuntunganBersihOutput.textContent = bersihText;
        if (summaryKeuntungan) summaryKeuntungan.textContent = keuntunganText;
        if (summaryPajak) summaryPajak.textContent = pajakText;
        if (summaryBersih) summaryBersih.textContent = bersihText;
    };

    [modalInput, nilaiPengajuanInput, biayaOperasionalInput, jumlahBarangInput].forEach((el) => {
        el?.addEventListener('input', calculate);
    });

    resetButton?.addEventListener('click', () => {
        if (modalInput) modalInput.value = 0;
        if (nilaiPengajuanInput) nilaiPengajuanInput.value = 0;
        if (biayaOperasionalInput) biayaOperasionalInput.value = 0;
        if (jumlahBarangInput) jumlahBarangInput.value = 1;
        calculate();
    });

    calculate();
</script>
@endsection
