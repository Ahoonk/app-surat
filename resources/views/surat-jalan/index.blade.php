@extends('layouts.app')

@section('content')
<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif
    <div>
        <h1 class="text-2xl font-semibold text-gray-800">Surat Jalan</h1>
        <p class="text-sm text-gray-500 mt-1">Data otomatis dibuat setiap invoice tercetak.</p>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        @if ($suratJalans->isEmpty())
            <p class="text-gray-500">Belum ada data surat jalan.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="border-b">
                            <th class="py-3 pr-4">Nomor Surat Jalan</th>
                            <th class="py-3 pr-4">Tanggal</th>
                            <th class="py-3 pr-4">No Invoice</th>
                            <th class="py-3 pr-4">Customer</th>
                            <th class="py-3 pr-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($suratJalans as $suratJalan)
                            <tr class="border-b">
                                <td class="py-3 pr-4">{{ $suratJalan->nomor }}</td>
                                <td class="py-3 pr-4">{{ \Illuminate\Support\Carbon::parse($suratJalan->tanggal)->translatedFormat('d F Y') }}</td>
                                <td class="py-3 pr-4">{{ $suratJalan->invoice->nomor }}</td>
                                <td class="py-3 pr-4">{{ $suratJalan->invoice->penawaran->to_company ?? $suratJalan->invoice->penawaran->customer_nama }}</td>
                                <td class="py-3 pr-4">
                                    <div class="action-buttons">
                                        <a href="{{ route('surat-jalan.edit', $suratJalan) }}" title="Ubah Data" class="action-icon action-icon-gray">&#9998;</a>
                                        <a href="{{ route('surat-jalan.show', $suratJalan) }}" title="Preview" class="action-icon action-icon-blue">&#128065;</a>
                                        <form method="POST" action="{{ route('surat-jalan.send', $suratJalan) }}" onsubmit="return confirm('Kirim surat jalan ke email customer?')">
                                            @csrf
                                            <button type="submit" title="Kirim" class="action-icon action-icon-gray">&#9993;</button>
                                        </form>
                                        @if (($suratJalan->invoice->penawaran->status ?? null) === 'draft')
                                            <form method="POST" action="{{ route('invoice.destroy', $suratJalan->invoice) }}" onsubmit="return confirm('Hapus transaksi yang dibatalkan ini?')">
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
