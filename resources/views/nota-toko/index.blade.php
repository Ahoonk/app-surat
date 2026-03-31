@extends('layouts.app')

@section('content')
<div>
    @if (session('success'))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Nota Toko</h1>
        <a href="{{ route('nota-toko.create') }}" class="bg-blue-600 text-white px-5 py-2 rounded-lg shadow hover:bg-blue-700 transition">+ Buat Nota Toko</a>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        @if ($notaTokos->isEmpty())
            <p class="text-gray-500">Belum ada data nota toko.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="border-b">
                            <th class="py-3 pr-4">Nomor</th>
                            <th class="py-3 pr-4">Tanggal</th>
                            <th class="py-3 pr-4">Customer</th>
                            <th class="py-3 pr-4">Total</th>
                            <th class="py-3 pr-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($notaTokos as $notaToko)
                            <tr class="border-b">
                                <td class="py-3 pr-4">{{ $notaToko->nomor }}</td>
                                <td class="py-3 pr-4">{{ \Illuminate\Support\Carbon::parse($notaToko->tanggal)->translatedFormat('d F Y') }}</td>
                                <td class="py-3 pr-4">{{ $notaToko->customer_nama }}</td>
                                <td class="py-3 pr-4">Rp {{ number_format($notaToko->total, 2, ',', '.') }}</td>
                                <td class="py-3 pr-4">
                                    <div class="action-buttons">
                                        <a href="{{ route('nota-toko.show', $notaToko) }}" title="Preview" class="action-icon action-icon-blue">&#128065;</a>
                                        <a href="{{ route('nota-toko.edit', $notaToko) }}" title="Ubah" class="action-icon action-icon-emerald">&#9998;</a>
                                        <form method="POST" action="{{ route('nota-toko.send', $notaToko) }}" onsubmit="return confirm('Kirim nota toko ke email customer?')">
                                            @csrf
                                            <button type="submit" title="Kirim" class="action-icon action-icon-gray">&#9993;</button>
                                        </form>
                                        <form method="POST" action="{{ route('nota-toko.destroy', $notaToko) }}" onsubmit="return confirm('Hapus nota toko ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus" class="action-icon action-icon-red">&#128465;</button>
                                        </form>
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
