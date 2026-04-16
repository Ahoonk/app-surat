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
        <h1 class="text-2xl font-semibold text-gray-800">
            Surat Penawaran
        </h1>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('penawaran.create') }}"
               class="bg-blue-600 text-white px-5 py-2 rounded-lg shadow hover:bg-blue-700 transition">
                + Buat Penawaran
            </a>
            <a href="{{ route('penawaran.mitra.create') }}"
               class="bg-emerald-600 text-white px-5 py-2 rounded-lg shadow hover:bg-emerald-700 transition">
                + Penawaran Mitra
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        @if ($penawarans->isEmpty())
            <p class="text-gray-500">
                Belum ada data penawaran.
            </p>
        @else
            <div class="space-y-3 md:hidden">
                @foreach ($penawarans as $penawaran)
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold">{{ $penawaran->nomor }}</div>
                        <div class="text-sm text-gray-600">{{ $penawaran->tanggal }}</div>
                        <div class="text-sm mt-1">{{ $penawaran->to_company ?? $penawaran->customer_nama }}</div>
                        <div class="text-sm capitalize">Jenis: {{ $penawaran->jenis_kontrak ?? 'satuan' }}</div>
                        <div class="text-sm">Total: Rp {{ number_format($penawaran->total, 2, ',', '.') }}</div>
                        <div class="text-sm capitalize">Status: {{ $penawaran->status }}</div>
                        <div class="mt-3 action-buttons text-sm">
                            <a href="{{ route('penawaran.show', $penawaran) }}" title="Preview" class="action-icon action-icon-gray">&#128065;</a>
                            <form method="POST" action="{{ route('penawaran.send', $penawaran) }}" onsubmit="return confirm('Kirim surat penawaran ke email customer?')">
                                @csrf
                                <button type="submit" title="Kirim" class="action-icon action-icon-gray">&#9993;</button>
                            </form>
                            @if (auth()->user()?->isSuperAdmin())
                                <a href="{{ route('penawaran.edit', $penawaran) }}" title="Ubah" class="action-icon action-icon-blue">&#9998;</a>
                            @endif
                            @if ($penawaran->status !== 'approved')
                                <button type="button" title="Verifikasi" class="action-icon action-icon-emerald approve-btn" data-action="{{ route('penawaran.approve-invoice', $penawaran) }}">
                                    &#10004;
                                </button>
                                <form method="POST" action="{{ route('penawaran.destroy', $penawaran) }}" onsubmit="return confirm('Hapus penawaran ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus" class="action-icon action-icon-red">&#128465;</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="border-b">
                            <th class="py-3 pr-4">Nomor</th>
                            <th class="py-3 pr-4">Tanggal</th>
                            <th class="py-3 pr-4">To</th>
                            <th class="py-3 pr-4">Jenis</th>
                            <th class="py-3 pr-4">Total</th>
                            <th class="py-3 pr-4">Status</th>
                            <th class="py-3 pr-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($penawarans as $penawaran)
                            <tr class="border-b">
                                <td class="py-3 pr-4">{{ $penawaran->nomor }}</td>
                                <td class="py-3 pr-4">{{ $penawaran->tanggal }}</td>
                                <td class="py-3 pr-4">{{ $penawaran->to_company ?? $penawaran->customer_nama }}</td>
                                <td class="py-3 pr-4 capitalize">{{ $penawaran->jenis_kontrak ?? 'satuan' }}</td>
                                <td class="py-3 pr-4">Rp {{ number_format($penawaran->total, 2, ',', '.') }}</td>
                                <td class="py-3 pr-4 capitalize">{{ $penawaran->status }}</td>
                                <td class="py-3 pr-4">
                                    <div class="action-buttons">
                                        <a href="{{ route('penawaran.show', $penawaran) }}"
                                           title="Preview"
                                           class="action-icon action-icon-gray hover:text-gray-900">
                                            &#128065;
                                        </a>
                                        <form method="POST" action="{{ route('penawaran.send', $penawaran) }}" onsubmit="return confirm('Kirim surat penawaran ke email customer?')">
                                            @csrf
                                            <button type="submit" title="Kirim" class="action-icon action-icon-gray hover:text-gray-900">
                                                &#9993;
                                            </button>
                                        </form>
                                        @if (auth()->user()?->isSuperAdmin())
                                            <a href="{{ route('penawaran.edit', $penawaran) }}"
                                               title="Ubah"
                                               class="action-icon action-icon-blue hover:text-blue-800">
                                                &#9998;
                                            </a>
                                        @endif
                                        @if ($penawaran->status !== 'approved')
                                            <button type="button"
                                                    title="Verifikasi"
                                                    class="action-icon action-icon-emerald hover:text-emerald-800 approve-btn"
                                                    data-action="{{ route('penawaran.approve-invoice', $penawaran) }}">
                                                &#10004;
                                            </button>
                                            <form method="POST" action="{{ route('penawaran.destroy', $penawaran) }}"
                                                  onsubmit="return confirm('Hapus penawaran ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        title="Hapus"
                                                        class="action-icon action-icon-red hover:text-red-800">
                                                    &#128465;
                                                </button>
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

<div id="approve-modal" class="fixed inset-0 bg-black/40 hidden items-center justify-center p-4 z-50">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Verifikasi Penawaran</h3>
        <p class="text-sm text-gray-600 mb-4">Setelah disetujui, proses lanjut dilakukan di menu Purchasing Order.</p>
        <form id="approve-form" method="POST">
            @csrf
            <div class="mt-6 flex justify-end gap-2">
                <button type="button" id="cancel-approve" class="px-4 py-2 bg-gray-200 rounded-lg">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Setujui Penawaran</button>
            </div>
        </form>
    </div>
</div>

<script>
    const approveModal = document.getElementById('approve-modal');
    const approveForm = document.getElementById('approve-form');
    const cancelApproveButton = document.getElementById('cancel-approve');

    document.querySelectorAll('.approve-btn').forEach((button) => {
        button.addEventListener('click', () => {
            approveForm.action = button.dataset.action;
            approveModal.classList.remove('hidden');
            approveModal.classList.add('flex');
        });
    });

    function closeApproveModal() {
        approveModal.classList.add('hidden');
        approveModal.classList.remove('flex');
    }

    cancelApproveButton.addEventListener('click', closeApproveModal);

    approveModal.addEventListener('click', (event) => {
        if (event.target === approveModal) {
            closeApproveModal();
        }
    });
</script>

@endsection
