<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SimulasiPembiayaanController extends Controller
{
    public function index()
    {
        return view('simulasi-pembiayaan.index');
    }

    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'modal' => ['required', 'numeric', 'min:0'],
            'nilai_pengajuan' => ['required', 'numeric', 'min:0'],
            'biaya_operasional' => ['required', 'numeric', 'min:0'],
            'jumlah_barang' => ['required', 'integer', 'min:1'],
        ]);

        $modal = (float) $validated['modal'];
        $nilaiPengajuan = (float) $validated['nilai_pengajuan'];
        $biayaOperasional = (float) $validated['biaya_operasional'];
        $jumlahBarang = (int) $validated['jumlah_barang'];
        $totalModal = $modal * $jumlahBarang;
        $totalPengajuan = $nilaiPengajuan * $jumlahBarang;
        $keuntungan = $totalPengajuan - $totalModal;
        $pajak = $keuntungan > 0 ? $keuntungan * 0.11 : 0;
        $keuntunganBersih = $keuntungan - $pajak - $biayaOperasional;

        return view('simulasi-pembiayaan.index', compact('modal', 'nilaiPengajuan', 'biayaOperasional', 'jumlahBarang', 'keuntungan', 'pajak', 'keuntunganBersih'));
    }
}
