<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ResolvesCompanyId;
use App\Mail\SuratJalanMail;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\SuratJalan;
use App\Services\DocumentTemplateResolver;
use App\Services\DocumentNumberService;
use App\Services\DocumentSnapshotService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class SuratJalanController extends Controller
{
    use ResolvesCompanyId;

    private function syncFromInvoices(int $companyId): void
    {
        $invoices = Invoice::whereHas('penawaran', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->with('penawaran.mitra')->get();

        foreach ($invoices as $invoice) {
            if (SuratJalan::where('invoice_id', $invoice->id)->exists()) {
                continue;
            }

            $mitra = $invoice->penawaran?->mitra;
            $numberService = app(DocumentNumberService::class);
            $nomor = $mitra?->nomor_surat_jalan
                ?: ($numberService->alderaNumberFromInvoice($invoice->nomor, 'SJ') ?? $numberService->next($companyId, 'surat_jalan', $invoice->tanggal));

            $suratJalan = SuratJalan::create([
                'company_id' => $companyId,
                'invoice_id' => $invoice->id,
                'nomor' => $nomor,
                'tanggal' => $invoice->tanggal,
                'created_by' => $invoice->created_by ?? auth()->id(),
            ]);

            $suratJalan->update([
                'snapshot_data' => app(DocumentSnapshotService::class)->forSuratJalan($suratJalan),
            ]);
        }
    }

    public function index(): JsonResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();

        $this->syncFromInvoices($companyId);

        $suratJalans = SuratJalan::whereHas('invoice.penawaran', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
            ->with(['invoice.penawaran.mitra', 'invoice.purchasingOrder', 'invoice.fakturPajak'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get()
            ->map(fn (SuratJalan $suratJalan) => $this->serializeSuratJalan($suratJalan));

        return response()->json([
            'data' => $suratJalans,
        ]);
    }

    public function show(SuratJalan $suratJalan): JsonResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if(! $suratJalan->invoice || ! $suratJalan->invoice->penawaran || $suratJalan->invoice->penawaran->company_id !== $companyId, 403);

        $suratJalan->load([
            'invoice.penawaran.company',
            'invoice.penawaran.user',
            'invoice.penawaran.approver',
            'invoice.penawaran.mitra',
            'invoice.penawaran.items',
            'invoice.purchasingOrder',
            'invoice.fakturPajak',
            'invoice.beritaAcara',
        ]);

        return response()->json([
            'data' => $this->serializeSuratJalan($suratJalan, true),
        ]);
    }

    public function send(SuratJalan $suratJalan): JsonResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if(! $suratJalan->invoice || ! $suratJalan->invoice->penawaran || $suratJalan->invoice->penawaran->company_id !== $companyId, 403);

        $suratJalan->load('invoice.penawaran.items');

        $customerName = $suratJalan->invoice->penawaran->to_company
            ?? $suratJalan->invoice->penawaran->customer_nama;

        $resolvedEmail = Customer::where('company_id', $companyId)
            ->where('nama', $customerName)
            ->value('email');

        if (empty($resolvedEmail)) {
            return response()->json([
                'message' => 'Email customer belum diisi.',
            ], 422);
        }

        $fileName = 'surat-jalan-' . str_replace('/', '-', $suratJalan->nomor) . '.pdf';
        $view = app(DocumentTemplateResolver::class)->resolveView($companyId, 'surat_jalan', 'surat-jalan.pdf');
        $pdf = Pdf::loadView($view, compact('suratJalan'))->setPaper('a4', 'portrait');
        $pdfData = $pdf->output();

        try {
            Mail::to($resolvedEmail)->send(new SuratJalanMail($suratJalan, $pdfData, $fileName));
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal mengirim email. Silakan cek konfigurasi email.',
            ], 500);
        }

        return response()->json([
            'message' => 'Surat jalan berhasil dikirim ke email customer.',
        ]);
    }

    private function serializeSuratJalan(SuratJalan $suratJalan, bool $includeInvoice = false): array
    {
        return [
            'id' => $suratJalan->id,
            'invoice_id' => $suratJalan->invoice_id,
            'nomor' => $suratJalan->nomor,
            'tanggal' => $suratJalan->tanggal,
            'pemberi_nama' => $suratJalan->pemberi_nama,
            'pemberi_jabatan' => $suratJalan->pemberi_jabatan,
            'pemberi_alamat' => $suratJalan->pemberi_alamat,
            'penerima_nama' => $suratJalan->penerima_nama,
            'penerima_hp' => $suratJalan->penerima_hp,
            'kota_tanggal_manual' => $suratJalan->kota_tanggal_manual,
            'created_by' => $suratJalan->created_by,
            'snapshot_data' => $suratJalan->snapshot_data,
            'invoice' => $includeInvoice && $suratJalan->relationLoaded('invoice') && $suratJalan->invoice ? [
                'id' => $suratJalan->invoice->id,
                'nomor' => $suratJalan->invoice->nomor,
                'tanggal' => $suratJalan->invoice->tanggal,
                'total' => (float) $suratJalan->invoice->total,
                'penawaran' => $suratJalan->invoice->relationLoaded('penawaran') && $suratJalan->invoice->penawaran ? [
                    'id' => $suratJalan->invoice->penawaran->id,
                    'nomor' => $suratJalan->invoice->penawaran->nomor,
                    'customer_nama' => $suratJalan->invoice->penawaran->customer_nama,
                    'to_company' => $suratJalan->invoice->penawaran->to_company,
                    'status' => $suratJalan->invoice->penawaran->status,
                    'total' => (float) $suratJalan->invoice->penawaran->total,
                    'snapshot_data' => $suratJalan->invoice->penawaran->snapshot_data,
                ] : null,
                'purchasing_order' => $suratJalan->invoice->relationLoaded('purchasingOrder') && $suratJalan->invoice->purchasingOrder ? [
                    'id' => $suratJalan->invoice->purchasingOrder->id,
                    'nomor_po' => $suratJalan->invoice->purchasingOrder->nomor_po,
                    'tanggal_po' => $suratJalan->invoice->purchasingOrder->tanggal_po,
                    'dokumen_name' => $suratJalan->invoice->purchasingOrder->dokumen_name,
                ] : null,
                'faktur_pajak' => $suratJalan->invoice->relationLoaded('fakturPajak') && $suratJalan->invoice->fakturPajak ? [
                    'id' => $suratJalan->invoice->fakturPajak->id,
                    'dokumen_name' => $suratJalan->invoice->fakturPajak->dokumen_name,
                    'payment_status' => $suratJalan->invoice->fakturPajak->payment_status,
                    'payment_date' => $suratJalan->invoice->fakturPajak->payment_date,
                ] : null,
                'berita_acara' => $suratJalan->invoice->relationLoaded('beritaAcara') && $suratJalan->invoice->beritaAcara ? [
                    'id' => $suratJalan->invoice->beritaAcara->id,
                    'nomor' => $suratJalan->invoice->beritaAcara->nomor,
                    'tanggal' => $suratJalan->invoice->beritaAcara->tanggal,
                ] : null,
            ] : null,
        ];
    }
}
