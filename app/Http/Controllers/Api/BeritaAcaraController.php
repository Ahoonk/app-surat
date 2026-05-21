<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ResolvesCompanyId;
use App\Mail\BeritaAcaraMail;
use App\Models\Customer;
use App\Models\BeritaAcara;
use App\Models\Invoice;
use App\Services\DocumentTemplateResolver;
use App\Services\DocumentNumberService;
use App\Services\DocumentSnapshotService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class BeritaAcaraController extends Controller
{
    use ResolvesCompanyId;

    private function syncFromInvoices(int $companyId): void
    {
        $invoices = Invoice::whereHas('penawaran', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->with('penawaran.mitra')->get();

        foreach ($invoices as $invoice) {
            if (BeritaAcara::where('invoice_id', $invoice->id)->exists()) {
                continue;
            }

            $mitra = $invoice->penawaran?->mitra;
            $nomor = $mitra?->nomor_berita_acara ?: app(DocumentNumberService::class)->next($companyId, 'berita_acara', $invoice->tanggal);

            $beritaAcara = BeritaAcara::create([
                'company_id' => $companyId,
                'invoice_id' => $invoice->id,
                'nomor' => $nomor,
                'tanggal' => $invoice->tanggal,
                'created_by' => $invoice->created_by ?? auth()->id(),
            ]);

            $beritaAcara->update([
                'snapshot_data' => app(DocumentSnapshotService::class)->forBeritaAcara($beritaAcara),
            ]);
        }
    }

    public function index(): JsonResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();

        try {
            $this->syncFromInvoices($companyId);
        } catch (\Throwable $e) {
            report($e);
        }

        $beritaAcaras = BeritaAcara::whereHas('invoice.penawaran', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
            ->with(['invoice.penawaran.mitra', 'invoice.purchasingOrder', 'invoice.suratJalan'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get()
            ->map(fn (BeritaAcara $beritaAcara) => $this->serializeBeritaAcara($beritaAcara));

        return response()->json([
            'data' => $beritaAcaras,
        ]);
    }

    public function show(BeritaAcara $beritaAcara): JsonResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if(! $beritaAcara->invoice || ! $beritaAcara->invoice->penawaran || $beritaAcara->invoice->penawaran->company_id !== $companyId, 403);

        $beritaAcara->load([
            'invoice.penawaran.company',
            'invoice.penawaran.user',
            'invoice.penawaran.approver',
            'invoice.penawaran.mitra',
            'invoice.penawaran.items',
            'invoice.purchasingOrder',
            'invoice.suratJalan',
        ]);

        return response()->json([
            'data' => $this->serializeBeritaAcara($beritaAcara, true),
        ]);
    }

    public function send(BeritaAcara $beritaAcara): JsonResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if(! $beritaAcara->invoice || ! $beritaAcara->invoice->penawaran || $beritaAcara->invoice->penawaran->company_id !== $companyId, 403);

        $beritaAcara->load('invoice.penawaran', 'invoice.purchasingOrder');

        $customerName = $beritaAcara->invoice->penawaran->to_company
            ?? $beritaAcara->invoice->penawaran->customer_nama;

        $resolvedEmail = Customer::where('company_id', $companyId)
            ->where('nama', $customerName)
            ->value('email');

        if (empty($resolvedEmail)) {
            return response()->json([
                'message' => 'Email customer belum diisi.',
            ], 422);
        }

        $fileName = 'berita-acara-' . str_replace('/', '-', $beritaAcara->nomor) . '.pdf';
        $view = app(DocumentTemplateResolver::class)->resolveView($companyId, 'berita_acara', 'berita-acara.pdf');
        $pdf = Pdf::loadView($view, compact('beritaAcara'))->setPaper('a4', 'portrait');
        $pdfData = $pdf->output();

        try {
            Mail::to($resolvedEmail)->send(new BeritaAcaraMail($beritaAcara, $pdfData, $fileName));
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal mengirim email. Silakan cek konfigurasi email.',
            ], 500);
        }

        return response()->json([
            'message' => 'Berita acara berhasil dikirim ke email customer.',
        ]);
    }

    private function serializeBeritaAcara(BeritaAcara $beritaAcara, bool $includeInvoice = false): array
    {
        return [
            'id' => $beritaAcara->id,
            'invoice_id' => $beritaAcara->invoice_id,
            'nomor' => $beritaAcara->nomor,
            'tanggal' => $beritaAcara->tanggal,
            'perihal' => $beritaAcara->perihal,
            'keterangan_akhir' => $beritaAcara->keterangan_akhir,
            'kota_tanggal_manual' => $beritaAcara->kota_tanggal_manual,
            'created_by' => $beritaAcara->created_by,
            'snapshot_data' => $beritaAcara->snapshot_data,
            'invoice' => $includeInvoice && $beritaAcara->relationLoaded('invoice') && $beritaAcara->invoice ? [
                'id' => $beritaAcara->invoice->id,
                'nomor' => $beritaAcara->invoice->nomor,
                'tanggal' => $beritaAcara->invoice->tanggal,
                'total' => (float) $beritaAcara->invoice->total,
                'penawaran' => $beritaAcara->invoice->relationLoaded('penawaran') && $beritaAcara->invoice->penawaran ? [
                    'id' => $beritaAcara->invoice->penawaran->id,
                    'nomor' => $beritaAcara->invoice->penawaran->nomor,
                    'customer_nama' => $beritaAcara->invoice->penawaran->customer_nama,
                    'to_company' => $beritaAcara->invoice->penawaran->to_company,
                    'status' => $beritaAcara->invoice->penawaran->status,
                    'total' => (float) $beritaAcara->invoice->penawaran->total,
                    'snapshot_data' => $beritaAcara->invoice->penawaran->snapshot_data,
                ] : null,
                'purchasing_order' => $beritaAcara->invoice->relationLoaded('purchasingOrder') && $beritaAcara->invoice->purchasingOrder ? [
                    'id' => $beritaAcara->invoice->purchasingOrder->id,
                    'nomor_po' => $beritaAcara->invoice->purchasingOrder->nomor_po,
                    'tanggal_po' => $beritaAcara->invoice->purchasingOrder->tanggal_po,
                    'dokumen_name' => $beritaAcara->invoice->purchasingOrder->dokumen_name,
                ] : null,
                'surat_jalan' => $beritaAcara->invoice->relationLoaded('suratJalan') && $beritaAcara->invoice->suratJalan ? [
                    'id' => $beritaAcara->invoice->suratJalan->id,
                    'nomor' => $beritaAcara->invoice->suratJalan->nomor,
                    'tanggal' => $beritaAcara->invoice->suratJalan->tanggal,
                ] : null,
            ] : null,
        ];
    }
}
