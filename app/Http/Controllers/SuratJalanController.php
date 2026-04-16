<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCompanyId;
use App\Mail\SuratJalanMail;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\SuratJalan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;

class SuratJalanController extends Controller
{
    use ResolvesCompanyId;

    public function index()
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $this->syncFromInvoices($companyId);

        $suratJalans = SuratJalan::whereHas('invoice.penawaran', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
            ->with('invoice.penawaran')
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        return view('surat-jalan.index', compact('suratJalans'));
    }

    public function show(SuratJalan $suratJalan)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $suratJalan->load('invoice.penawaran.items');
        abort_if(!$suratJalan->invoice?->penawaran || $suratJalan->invoice->penawaran->company_id !== $companyId, 403);

        return view('surat-jalan.show', compact('suratJalan'));
    }

    public function edit(SuratJalan $suratJalan)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $suratJalan->load('invoice.penawaran.items');
        abort_if(!$suratJalan->invoice?->penawaran || $suratJalan->invoice->penawaran->company_id !== $companyId, 403);

        return view('surat-jalan.edit', compact('suratJalan'));
    }

    public function pdf(SuratJalan $suratJalan)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $suratJalan->load('invoice.penawaran.items');
        abort_if(!$suratJalan->invoice?->penawaran || $suratJalan->invoice->penawaran->company_id !== $companyId, 403);

        $fileName = 'surat-jalan-' . str_replace('/', '-', $suratJalan->nomor) . '.pdf';
        $pdf = Pdf::loadView('surat-jalan.pdf', compact('suratJalan'))->setPaper('a4', 'portrait');

        if (request()->boolean('download')) {
            return $pdf->download($fileName);
        }

        return $pdf->stream($fileName);
    }

    public function send(SuratJalan $suratJalan)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $suratJalan->load('invoice.penawaran.items');
        abort_if(!$suratJalan->invoice?->penawaran || $suratJalan->invoice->penawaran->company_id !== $companyId, 403);

        $customerName = $suratJalan->invoice->penawaran->to_company
            ?? $suratJalan->invoice->penawaran->customer_nama;
        $resolvedEmail = Customer::where('company_id', $companyId)
            ->where('nama', $customerName)
            ->value('email');

        if (empty($resolvedEmail)) {
            return back()->with('error', 'Email customer belum diisi.');
        }

        $fileName = 'surat-jalan-' . str_replace('/', '-', $suratJalan->nomor) . '.pdf';
        $pdf = Pdf::loadView('surat-jalan.pdf', compact('suratJalan'))->setPaper('a4', 'portrait');
        $pdfData = $pdf->output();

        try {
            Mail::to($resolvedEmail)->send(new SuratJalanMail($suratJalan, $pdfData, $fileName));
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengirim email. Silakan cek konfigurasi email.');
        }

        return back()->with('success', 'Surat jalan berhasil dikirim ke email customer.');
    }

    public function update(\Illuminate\Http\Request $request, SuratJalan $suratJalan)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $suratJalan->load('invoice.penawaran');
        abort_if(!$suratJalan->invoice?->penawaran || $suratJalan->invoice->penawaran->company_id !== $companyId, 403);

        $validated = $request->validate([
            'pemberi_nama' => ['nullable', 'string', 'max:255'],
            'pemberi_jabatan' => ['nullable', 'string', 'max:255'],
            'pemberi_alamat' => ['nullable', 'string'],
            'penerima_nama' => ['nullable', 'string', 'max:255'],
            'penerima_hp' => ['nullable', 'string', 'max:50'],
            'kota_tanggal_manual' => ['nullable', 'date'],
        ]);

        $suratJalan->update($validated);

        return redirect()->route('surat-jalan.edit', $suratJalan)->with('success', 'Keterangan surat jalan berhasil diperbarui.');
    }

    private function syncFromInvoices(int $companyId): void
    {
        $invoices = Invoice::whereHas('penawaran', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->get();

        foreach ($invoices as $invoice) {
            $mitra = $invoice->penawaran?->mitra;
            $nomor = $mitra?->nomor_surat_jalan ?: preg_replace('/^INV\//', 'SJ/', $invoice->nomor);

            SuratJalan::firstOrCreate(
                ['invoice_id' => $invoice->id],
                [
                    'nomor' => $nomor,
                    'tanggal' => $invoice->tanggal,
                    'created_by' => $invoice->created_by ?? auth()->id(),
                ]
            );
        }
    }
}
