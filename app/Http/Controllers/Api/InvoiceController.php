<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ResolvesCompanyId;
use App\Mail\InvoiceMail;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\SuratJalan;
use App\Services\DocumentTemplateResolver;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class InvoiceController extends Controller
{
    use ResolvesCompanyId;

    private function loadInvoiceRelations(Invoice $invoice): Invoice
    {
        return $invoice->load([
            'penawaran.company',
            'penawaran.user',
            'penawaran.approver',
            'penawaran.mitra',
            'penawaran.items',
            'purchasingOrder',
            'fakturPajak',
            'suratJalan',
            'beritaAcara',
        ]);
    }

    public function index(): JsonResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();

        $invoices = Invoice::whereHas('penawaran', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
            ->with(['penawaran', 'fakturPajak', 'suratJalan', 'beritaAcara'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Invoice $invoice) => $this->serializeInvoice($invoice));

        return response()->json([
            'data' => $invoices,
        ]);
    }

    public function show(Invoice $invoice): JsonResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if(! $invoice->penawaran || $invoice->penawaran->company_id !== $companyId, 403);

        $this->loadInvoiceRelations($invoice);

        return response()->json([
            'data' => $this->serializeInvoice($invoice, true),
        ]);
    }

    public function send(Invoice $invoice): JsonResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();

        $invoice->load('penawaran.items', 'purchasingOrder');
        abort_if(! $invoice->penawaran || $invoice->penawaran->company_id !== $companyId, 403);

        $customerName = $invoice->penawaran->to_company ?? $invoice->penawaran->customer_nama;
        $resolvedEmail = Customer::where('company_id', $companyId)
            ->where('nama', $customerName)
            ->value('email');

        if (empty($resolvedEmail)) {
            return response()->json([
                'message' => 'Email customer belum diisi.',
            ], 422);
        }

        $fileName = 'invoice-' . str_replace('/', '-', $invoice->nomor) . '.pdf';
        $view = app(DocumentTemplateResolver::class)->resolveView($companyId, 'invoice', 'invoice.pdf');
        $pdf = Pdf::loadView($view, [
            'invoice' => $invoice,
            'penawaran' => $invoice->penawaran,
        ])->setPaper('a4', 'portrait');
        $pdfData = $pdf->output();

        try {
            Mail::to($resolvedEmail)->send(new InvoiceMail($invoice, $pdfData, $fileName));
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal mengirim email. Silakan cek konfigurasi email.',
            ], 500);
        }

        return response()->json([
            'message' => 'Invoice berhasil dikirim ke email customer.',
        ]);
    }

    public function updatePrintDate(Request $request, Invoice $invoice): JsonResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();

        $invoice->load('penawaran.mitra');
        abort_if(! $invoice->penawaran || $invoice->penawaran->company_id !== $companyId, 403);

        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
        ]);

        $mitra = $invoice->penawaran->mitra;
        $numberService = app(\App\Services\DocumentNumberService::class);
        $newNomor = $mitra?->nomor_invoice
            ? $invoice->nomor
            : $this->rebuildInvoiceNumberWithDate($invoice->nomor, $validated['tanggal']);

        DB::transaction(function () use ($invoice, $validated, $newNomor, $mitra, $numberService) {
            $invoice->update([
                'tanggal' => $validated['tanggal'],
                'nomor' => $newNomor,
            ]);

            $suratJalanNomor = $mitra?->nomor_surat_jalan
                ? $mitra->nomor_surat_jalan
                : ($numberService->alderaNumberFromInvoice($newNomor, 'SJ') ?? preg_replace('/^INV\//', 'SJ/', $newNomor));

            SuratJalan::where('invoice_id', $invoice->id)->update([
                'tanggal' => $validated['tanggal'],
                'nomor' => $suratJalanNomor,
            ]);
        });

        $this->loadInvoiceRelations($invoice);
        $invoice->update([
            'snapshot_data' => app(\App\Services\DocumentSnapshotService::class)->forInvoice($invoice),
        ]);

        return response()->json([
            'message' => 'Tanggal cetak invoice berhasil diperbarui.',
            'data' => $this->serializeInvoice($invoice, true),
        ]);
    }

    public function verifyPayment(Request $request, Invoice $invoice): JsonResponse
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $companyId = $this->getCompanyIdOrRedirect();
        $invoice->load('penawaran');
        abort_if(! $invoice->penawaran || $invoice->penawaran->company_id !== $companyId, 403);

        $validated = $request->validate([
            'payment_date' => ['required', 'date'],
        ]);

        $invoice->update([
            'payment_status' => 'paid',
            'payment_date' => $validated['payment_date'],
        ]);

        $this->loadInvoiceRelations($invoice);
        $invoice->update([
            'snapshot_data' => app(\App\Services\DocumentSnapshotService::class)->forInvoice($invoice),
        ]);

        return response()->json([
            'message' => 'Status pembayaran invoice berhasil diubah menjadi sudah dibayarkan.',
            'data' => $this->serializeInvoice($invoice, true),
        ]);
    }

    private function serializeInvoice(Invoice $invoice, bool $includeDocuments = false): array
    {
        return [
            'id' => $invoice->id,
            'penawaran_id' => $invoice->penawaran_id,
            'purchasing_order_id' => $invoice->purchasing_order_id,
            'nomor' => $invoice->nomor,
            'tanggal' => $invoice->tanggal,
            'sequence' => $invoice->sequence,
            'total' => (float) $invoice->total,
            'payment_status' => $invoice->payment_status,
            'payment_date' => $invoice->payment_date,
            'created_by' => $invoice->created_by,
            'snapshot_data' => $invoice->snapshot_data,
            'penawaran' => $invoice->relationLoaded('penawaran') && $invoice->penawaran ? [
                'id' => $invoice->penawaran->id,
                'company_id' => $invoice->penawaran->company_id,
                'nomor' => $invoice->penawaran->nomor,
                'tanggal' => $invoice->penawaran->tanggal,
                'customer_nama' => $invoice->penawaran->customer_nama,
                'to_company' => $invoice->penawaran->to_company,
                'jenis_kontrak' => $invoice->penawaran->jenis_kontrak,
                'status' => $invoice->penawaran->status,
                'subtotal' => (float) $invoice->penawaran->subtotal,
                'tax_percent' => (float) $invoice->penawaran->tax_percent,
                'tax_amount' => (float) $invoice->penawaran->tax_amount,
                'total' => (float) $invoice->penawaran->total,
                'snapshot_data' => $invoice->penawaran->snapshot_data,
                'items' => $includeDocuments ? $invoice->penawaran->items->map(fn ($item) => [
                    'id' => $item->id,
                    'nama' => $item->nama,
                    'rincian' => $item->rincian,
                    'qty' => (float) $item->qty,
                    'satuan' => $item->satuan,
                    'unit_price' => (float) $item->unit_price,
                    'amount' => (float) $item->amount,
                ]) : null,
                'mitra' => $includeDocuments && $invoice->penawaran->relationLoaded('mitra') && $invoice->penawaran->mitra ? [
                    'id' => $invoice->penawaran->mitra->id,
                    'nama' => $invoice->penawaran->mitra->nama,
                    'email' => $invoice->penawaran->mitra->email,
                    'alamat' => $invoice->penawaran->mitra->alamat,
                ] : null,
            ] : null,
            'purchasing_order' => $invoice->relationLoaded('purchasingOrder') && $invoice->purchasingOrder ? [
                'id' => $invoice->purchasingOrder->id,
                'dokumen_path' => $invoice->purchasingOrder->dokumen_path,
                'dokumen_name' => $invoice->purchasingOrder->dokumen_name,
                'nomor_po' => $invoice->purchasingOrder->nomor_po,
                'tanggal_po' => $invoice->purchasingOrder->tanggal_po,
            ] : null,
            'faktur_pajak' => $invoice->relationLoaded('fakturPajak') && $invoice->fakturPajak ? [
                'id' => $invoice->fakturPajak->id,
                'dokumen_path' => $invoice->fakturPajak->dokumen_path,
                'dokumen_name' => $invoice->fakturPajak->dokumen_name,
                'payment_status' => $invoice->fakturPajak->payment_status,
                'payment_date' => $invoice->fakturPajak->payment_date,
            ] : null,
            'surat_jalan' => $invoice->relationLoaded('suratJalan') && $invoice->suratJalan ? [
                'id' => $invoice->suratJalan->id,
                'nomor' => $invoice->suratJalan->nomor,
                'tanggal' => $invoice->suratJalan->tanggal,
                'pemberi_nama' => $invoice->suratJalan->pemberi_nama,
                'pemberi_jabatan' => $invoice->suratJalan->pemberi_jabatan,
                'penerima_nama' => $invoice->suratJalan->penerima_nama,
                'penerima_hp' => $invoice->suratJalan->penerima_hp,
            ] : null,
            'berita_acara' => $invoice->relationLoaded('beritaAcara') && $invoice->beritaAcara ? [
                'id' => $invoice->beritaAcara->id,
                'nomor' => $invoice->beritaAcara->nomor,
                'tanggal' => $invoice->beritaAcara->tanggal,
                'perihal' => $invoice->beritaAcara->perihal,
                'keterangan_akhir' => $invoice->beritaAcara->keterangan_akhir,
            ] : null,
        ];
    }

    private function rebuildInvoiceNumberWithDate(string $currentNumber, string $newDate): string
    {
        $date = Carbon::parse($newDate);
        $running = '001';

        if (preg_match('/^INV\/\d{4}\/\d{2}\/(\d+)-ASK$/', $currentNumber, $match)) {
            $running = $match[1];
        }

        return sprintf('INV/%s/%s/%s-ASK', $date->format('Y'), $date->format('m'), $running);
    }
}
