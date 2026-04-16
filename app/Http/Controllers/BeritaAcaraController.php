<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCompanyId;
use App\Mail\BeritaAcaraMail;
use App\Models\BeritaAcara;
use App\Models\Customer;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BeritaAcaraController extends Controller
{
    use ResolvesCompanyId;

    public function index()
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        try {
            $this->syncFromInvoices($companyId);
        } catch (\Throwable $e) {
            Log::error('Gagal sinkron berita acara dari invoice.', [
                'company_id' => $companyId,
                'message' => $e->getMessage(),
            ]);
        }

        $beritaAcaras = BeritaAcara::whereHas('invoice.penawaran', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
            ->with('invoice.penawaran')
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        return view('berita-acara.index', compact('beritaAcaras'));
    }

    public function show(BeritaAcara $beritaAcara)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $beritaAcara->load('invoice.penawaran', 'invoice.purchasingOrder');
        abort_if(!$beritaAcara->invoice?->penawaran || $beritaAcara->invoice->penawaran->company_id !== $companyId, 403);

        return view('berita-acara.show', compact('beritaAcara'));
    }

    public function edit(BeritaAcara $beritaAcara)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $beritaAcara->load('invoice.penawaran', 'invoice.purchasingOrder');
        abort_if(!$beritaAcara->invoice?->penawaran || $beritaAcara->invoice->penawaran->company_id !== $companyId, 403);

        return view('berita-acara.edit', compact('beritaAcara'));
    }

    public function update(Request $request, BeritaAcara $beritaAcara)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $beritaAcara->load('invoice.penawaran');
        abort_if(!$beritaAcara->invoice?->penawaran || $beritaAcara->invoice->penawaran->company_id !== $companyId, 403);

        $validated = $request->validate([
            'perihal' => ['nullable', 'string', 'max:255'],
            'keterangan_akhir' => ['nullable', 'string'],
            'kota_tanggal_manual' => ['nullable', 'date'],
        ]);

        $beritaAcara->update($validated);

        return redirect()->route('berita-acara.edit', $beritaAcara)->with('success', 'Berita Acara berhasil diperbarui.');
    }

    public function pdf(BeritaAcara $beritaAcara)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $beritaAcara->load('invoice.penawaran', 'invoice.purchasingOrder');
        abort_if(!$beritaAcara->invoice?->penawaran || $beritaAcara->invoice->penawaran->company_id !== $companyId, 403);

        $fileName = 'berita-acara-' . str_replace('/', '-', $beritaAcara->nomor) . '.pdf';
        $pdf = Pdf::loadView('berita-acara.pdf', compact('beritaAcara'))->setPaper('a4', 'portrait');

        if (request()->boolean('download')) {
            return $pdf->download($fileName);
        }

        return $pdf->stream($fileName);
    }

    public function send(BeritaAcara $beritaAcara)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $beritaAcara->load('invoice.penawaran', 'invoice.purchasingOrder');
        abort_if(!$beritaAcara->invoice?->penawaran || $beritaAcara->invoice->penawaran->company_id !== $companyId, 403);

        $customerName = $beritaAcara->invoice->penawaran->to_company
            ?? $beritaAcara->invoice->penawaran->customer_nama;
        $resolvedEmail = Customer::where('company_id', $companyId)
            ->where('nama', $customerName)
            ->value('email');

        if (empty($resolvedEmail)) {
            return back()->with('error', 'Email customer belum diisi.');
        }

        $fileName = 'berita-acara-' . str_replace('/', '-', $beritaAcara->nomor) . '.pdf';
        $pdf = Pdf::loadView('berita-acara.pdf', compact('beritaAcara'))->setPaper('a4', 'portrait');
        $pdfData = $pdf->output();

        try {
            Mail::to($resolvedEmail)->send(new BeritaAcaraMail($beritaAcara, $pdfData, $fileName));
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengirim email. Silakan cek konfigurasi email.');
        }

        return back()->with('success', 'Berita acara berhasil dikirim ke email customer.');
    }

    private function syncFromInvoices(int $companyId): void
    {
        $invoices = Invoice::whereHas('penawaran', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->with('penawaran.mitra')->get();

        foreach ($invoices as $invoice) {
            $baseNumber = $this->buildBeritaAcaraNumber($invoice);
            $nomor = $this->resolveUniqueBeritaAcaraNumber($invoice->id, $baseNumber);

            BeritaAcara::updateOrCreate(
                ['invoice_id' => $invoice->id],
                [
                    'nomor' => $nomor,
                    'tanggal' => $invoice->tanggal,
                    'created_by' => $invoice->created_by ?? auth()->id(),
                ]
            );
        }
    }

    private function buildBeritaAcaraNumber(Invoice $invoice): string
    {
        $mitra = $invoice->penawaran?->mitra;

        if (!empty($mitra?->nomor_berita_acara)) {
            return $mitra->nomor_berita_acara;
        }

        return preg_replace('/^INV\//', 'BA/', $invoice->nomor);
    }

    private function resolveUniqueBeritaAcaraNumber(int $invoiceId, string $baseNumber): string
    {
        $candidate = $baseNumber;
        $suffix = 2;

        while (
            BeritaAcara::where('nomor', $candidate)
                ->where('invoice_id', '!=', $invoiceId)
                ->exists()
        ) {
            $candidate = $baseNumber . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }
}
