@extends('layouts.app')

@section('content')
<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    <div>
        <h1 class="text-2xl font-semibold text-gray-800">Berita Acara</h1>
        <p class="text-sm text-gray-500 mt-1">Otomatis muncul setelah invoice tercetak dari Purchasing Order.</p>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        @if ($beritaAcaras->isEmpty())
            <p class="text-gray-500">Belum ada data berita acara.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="border-b">
                            <th class="py-3 pr-4">Nomor</th>
                            <th class="py-3 pr-4">Tanggal</th>
                            <th class="py-3 pr-4">No Invoice</th>
                            <th class="py-3 pr-4">Customer</th>
                            <th class="py-3 pr-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($beritaAcaras as $beritaAcara)
                            @php
                                $invoice = $beritaAcara->invoice;
                                $penawaran = $invoice?->penawaran;
                            @endphp
                            <tr class="border-b">
                                <td class="py-3 pr-4">{{ $beritaAcara->nomor }}</td>
                                <td class="py-3 pr-4">{{ \Illuminate\Support\Carbon::parse($beritaAcara->tanggal)->translatedFormat('d F Y') }}</td>
                                <td class="py-3 pr-4">{{ $invoice?->nomor ?? '-' }}</td>
                                <td class="py-3 pr-4">{{ $penawaran?->to_company ?? $penawaran?->customer_nama ?? '-' }}</td>
                                <td class="py-3 pr-4">
                                    <div class="action-buttons">
                                        <a href="{{ route('berita-acara.edit', $beritaAcara) }}" title="Ubah Data" class="action-icon action-icon-gray">&#9998;</a>
                                        <a href="{{ route('berita-acara.show', $beritaAcara) }}" title="Preview" class="action-icon action-icon-blue">&#128065;</a>
                                        <form method="POST" action="{{ route('berita-acara.send', $beritaAcara) }}" onsubmit="return confirm('Kirim berita acara ke email customer?')">
                                            @csrf
                                            <button type="submit" title="Kirim" class="action-icon action-icon-gray">&#9993;</button>
                                        </form>
                                        @if (($penawaran?->status ?? null) === 'draft')
                                            <form method="POST" action="{{ route('invoice.destroy', $invoice) }}" onsubmit="return confirm('Hapus transaksi yang dibatalkan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Hapus Transaksi" class="action-icon action-icon-red">&#128465;</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
