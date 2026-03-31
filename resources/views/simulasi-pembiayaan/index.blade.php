@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-800">Simulasi Pembiayaan</h1>
        <p class="text-sm text-gray-500 mt-1">Isi modal, nilai pengajuan, jumlah barang, dan biaya operasional, lalu sistem hitung keuntungan, pajak 11%, dan keuntungan bersih.</p>
        <p class="text-xs text-gray-500 mt-1">Currency: Rupiah Indonesia (IDR)</p>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <div class="grid grid-cols-1 gap-4">
            <div>
                <label class="block text-sm mb-1">Modal (Rp)</label>
                <div class="flex items-center border rounded-lg px-3 py-2">
                    <span class="text-sm text-gray-500 mr-2">Rp</span>
                    <input id="modal" type="number" name="modal" min="0" step="0.01" value="{{ old('modal', $modal ?? 0) }}" class="w-full border-0 p-0 focus:ring-0">
                </div>
            </div>
            <div>
                <label class="block text-sm mb-1">Nilai Pengajuan (Rp)</label>
                <div class="flex items-center border rounded-lg px-3 py-2">
                    <span class="text-sm text-gray-500 mr-2">Rp</span>
                    <input id="nilai-pengajuan" type="number" name="nilai_pengajuan" min="0" step="0.01" value="{{ old('nilai_pengajuan', $nilaiPengajuan ?? 0) }}" class="w-full border-0 p-0 focus:ring-0">
                </div>
            </div>
            <div>
                <label class="block text-sm mb-1">Biaya Operasional (Rp)</label>
                <div class="flex items-center border rounded-lg px-3 py-2">
                    <span class="text-sm text-gray-500 mr-2">Rp</span>
                    <input id="biaya-operasional" type="number" name="biaya_operasional" min="0" step="0.01" value="{{ old('biaya_operasional', $biayaOperasional ?? 0) }}" class="w-full border-0 p-0 focus:ring-0">
                </div>
            </div>
            <div>
                <label class="block text-sm mb-1">Jumlah Barang</label>
                <div class="flex items-center border rounded-lg px-3 py-2">
                    <input id="jumlah-barang" type="number" name="jumlah_barang" min="1" step="1" value="{{ old('jumlah_barang', $jumlahBarang ?? 1) }}" class="w-full border-0 p-0 focus:ring-0">
                </div>
            </div>
            <div>
                <label class="block text-sm mb-1">Keuntungan</label>
                <input type="text"
                       id="keuntungan"
                       value="Rp {{ number_format(($keuntungan ?? 0), 2, ',', '.') }}"
                       readonly
                       class="w-full border rounded-lg px-3 py-2 bg-gray-100">
            </div>
            <div>
                <label class="block text-sm mb-1">Pajak (11%)</label>
                <input type="text"
                       id="pajak"
                       value="Rp {{ number_format(($pajak ?? 0), 2, ',', '.') }}"
                       readonly
                       class="w-full border rounded-lg px-3 py-2 bg-gray-100">
            </div>
            <div>
                <label class="block text-sm mb-1">Keuntungan Bersih</label>
                <input type="text"
                       id="keuntungan-bersih"
                       value="Rp {{ number_format(($keuntunganBersih ?? 0), 2, ',', '.') }}"
                       readonly
                       class="w-full border rounded-lg px-3 py-2 bg-gray-100">
            </div>
        </div>
    </div>
</div>

<script>
    const modalInput = document.getElementById('modal');
    const nilaiPengajuanInput = document.getElementById('nilai-pengajuan');
    const biayaOperasionalInput = document.getElementById('biaya-operasional');
    const jumlahBarangInput = document.getElementById('jumlah-barang');
    const keuntunganOutput = document.getElementById('keuntungan');
    const pajakOutput = document.getElementById('pajak');
    const keuntunganBersihOutput = document.getElementById('keuntungan-bersih');

    const formatRupiah = (value) => new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
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

        keuntunganOutput.value = formatRupiah(keuntungan);
        pajakOutput.value = formatRupiah(pajak);
        keuntunganBersihOutput.value = formatRupiah(keuntunganBersih);
    };

    [modalInput, nilaiPengajuanInput, biayaOperasionalInput, jumlahBarangInput].forEach((el) => {
        el?.addEventListener('input', calculate);
    });

    calculate();
</script>
@endsection
