<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCompanyId;
use App\Models\Invoice;
use App\Models\Penawaran;
use App\Models\PurchasingOrder;
use App\Models\SuratJalan;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class PurchasingOrderController extends Controller
{
    use ResolvesCompanyId;

    private function companyPenawarans(int $companyId): Builder
    {
        return Penawaran::where('company_id', $companyId);
    }

    public function index()
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $approvedSatuan = $this->companyPenawarans($companyId)
            ->where('status', 'approved')
            ->where('jenis_kontrak', 'satuan')
            ->whereDoesntHave('purchasingOrder')
            ->latest()
            ->get();

        $approvedKontrak = $this->companyPenawarans($companyId)
            ->where('status', 'approved')
            ->where('jenis_kontrak', 'kontrak')
            ->whereDoesntHave('purchasingOrder')
            ->latest()
            ->get();

        $existingData = $this->companyPenawarans($companyId)
            ->where('status', 'approved')
            ->whereHas('purchasingOrder')
            ->with([
                'purchasingOrder',
                'invoices' => function ($query) {
                    $query->orderByDesc('sequence')->orderByDesc('id');
                },
            ])
            ->latest()
            ->get();

        return view('purchasing-order.index', compact('approvedSatuan', 'approvedKontrak', 'existingData'));
    }

    public function store(Request $request)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $validated = $request->validate([
            'penawaran_id' => ['required', 'exists:penawarans,id'],
            'nomor_po' => ['required', 'string', 'max:100'],
            'tanggal_po' => ['required', 'date'],
            'dokumen' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $penawaran = $this->companyPenawarans($companyId)
            ->where('status', 'approved')
            ->where('id', $validated['penawaran_id'])
            ->firstOrFail();

        if ($penawaran->purchasingOrder) {
            return back()->with('status', 'Dokumen Purchasing Order untuk penawaran ini sudah pernah diupload.');
        }

        $file = $request->file('dokumen');
        $path = $file->store('purchasing-orders', 'public');

        PurchasingOrder::create([
            'penawaran_id' => $penawaran->id,
            'dokumen_path' => $path,
            'dokumen_name' => $file->getClientOriginalName(),
            'nomor_po' => $validated['nomor_po'],
            'tanggal_po' => $validated['tanggal_po'],
            'uploaded_by' => auth()->id(),
            'uploaded_at' => now(),
        ]);

        return redirect()->route('purchasing-order.index')
            ->with('success', 'Dokumen PO berhasil diupload. Lanjutkan klik Cetak Invoice pada daftar dokumen.');
    }

    public function createInvoice(Penawaran $penawaran)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        abort_if($penawaran->company_id !== $companyId, 403);
        abort_if($penawaran->status !== 'approved', 403);
        abort_if(!$penawaran->purchasingOrder, 403);
        abort_if($penawaran->invoices()->exists(), 403);

        $sequence = 1;
        $invoiceDate = now()->toDateString();
        $mitra = $penawaran->mitra;
        $invoiceNumber = $mitra?->nomor_invoice ?: $this->buildInvoiceNumber($invoiceDate);

        $invoice = Invoice::create([
            'penawaran_id' => $penawaran->id,
            'purchasing_order_id' => $penawaran->purchasingOrder->id,
            'nomor' => $invoiceNumber,
            'tanggal' => $invoiceDate,
            'sequence' => $sequence,
            'total' => $penawaran->total,
            'created_by' => auth()->id(),
        ]);

        $suratJalanNomor = $mitra?->nomor_surat_jalan ?: preg_replace('/^INV\//', 'SJ/', $invoice->nomor);

        SuratJalan::firstOrCreate(
            ['invoice_id' => $invoice->id],
            [
                'nomor' => $suratJalanNomor,
                'tanggal' => $invoiceDate,
                'created_by' => auth()->id(),
            ]
        );

        $penawaran->update([
            'invoice_date' => $invoiceDate,
            'invoice_sequence' => $sequence,
            'invoice_number' => $invoiceNumber,
        ]);

        return redirect()->route('invoice.pdf', ['invoice' => $invoice, 'download' => 1]);
    }

    public function nextInvoice(Request $request, Penawaran $penawaran)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        abort_if($penawaran->company_id !== $companyId, 403);
        abort_if($penawaran->status !== 'approved', 403);
        abort_if($penawaran->jenis_kontrak !== 'kontrak', 403);
        abort_if(!$penawaran->purchasingOrder, 403);

        $validated = $request->validate([
            'invoice_date' => ['required', 'date'],
        ]);

        $latestSequence = (int) $penawaran->invoices()->max('sequence');
        abort_if($latestSequence < 1, 403);
        $currentSequence = max((int) $penawaran->invoice_sequence, $latestSequence, 1);
        $nextSequence = $currentSequence + 1;
        $mitra = $penawaran->mitra;
        $invoiceNumber = $mitra?->nomor_invoice ?: $this->buildInvoiceNumber($validated['invoice_date']);

        $invoice = Invoice::create([
            'penawaran_id' => $penawaran->id,
            'purchasing_order_id' => $penawaran->purchasingOrder->id,
            'nomor' => $invoiceNumber,
            'tanggal' => $validated['invoice_date'],
            'sequence' => $nextSequence,
            'total' => $penawaran->total,
            'created_by' => auth()->id(),
        ]);

        $suratJalanNomor = $mitra?->nomor_surat_jalan ?: preg_replace('/^INV\//', 'SJ/', $invoice->nomor);

        SuratJalan::firstOrCreate(
            ['invoice_id' => $invoice->id],
            [
                'nomor' => $suratJalanNomor,
                'tanggal' => $validated['invoice_date'],
                'created_by' => auth()->id(),
            ]
        );

        $penawaran->update([
            'invoice_sequence' => $nextSequence,
            'invoice_date' => $validated['invoice_date'],
            'invoice_number' => $invoiceNumber,
        ]);

        return redirect()->route('invoice.pdf', ['invoice' => $invoice, 'download' => 1]);
    }

    public function cancelApproved(Penawaran $penawaran)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        abort_if($penawaran->company_id !== $companyId, 403);
        abort_if($penawaran->status !== 'approved', 403);
        abort_if($penawaran->purchasingOrder, 403);

        $penawaran->update([
            'status' => 'submitted',
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return redirect()->route('purchasing-order.index')
            ->with('success', 'Status penawaran dikembalikan ke submitted.');
    }

    private function buildInvoiceNumber(string $invoiceDate): string
    {
        $date = \Illuminate\Support\Carbon::parse($invoiceDate);
        $runningNumber = $this->nextInvoiceRunningNumber();

        return sprintf(
            'INV/%s/%s/%s-ASK',
            $date->format('Y'),
            $date->format('m'),
            str_pad((string) $runningNumber, 3, '0', STR_PAD_LEFT)
        );
    }

    private function nextInvoiceRunningNumber(): int
    {
        $maxRunning = Invoice::query()
            ->pluck('nomor')
            ->map(function ($nomor) {
                if (preg_match('/^INV\/\d{4}\/\d{2}\/(\d{3})-ASK$/', $nomor, $match)) {
                    return (int) $match[1];
                }

                return null;
            })
            ->filter()
            ->max();

        // Existing manual invoice stops at 002, so app starts from 003.
        return max((int) $maxRunning, 2) + 1;
    }
}
