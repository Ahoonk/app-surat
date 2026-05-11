<?php

namespace App\Http\Controllers\Api;

use App\Mail\PenawaranMail;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ResolvesCompanyId;
use App\Models\Customer;
use App\Models\Mitra;
use App\Models\Penawaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PenawaranController extends Controller
{
    use ResolvesCompanyId;

    private function companyPenawarans(int $companyId): Builder
    {
        return Penawaran::where('company_id', $companyId);
    }

    private function loadPenawaranRelations(Penawaran $penawaran): Penawaran
    {
        return $penawaran->load([
            'company:id,name,address,logo',
            'user:id,name,email',
            'approver:id,name,email',
            'mitra',
            'items',
            'purchasingOrder',
            'invoices' => function ($query) {
                $query->orderByDesc('tanggal')->orderByDesc('id');
            },
            'invoices.purchasingOrder',
            'invoices.fakturPajak',
            'invoices.suratJalan',
            'invoices.beritaAcara',
        ]);
    }

    private function monthCode(int $month): string
    {
        $codes = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ];

        return $codes[$month] ?? 'I';
    }

    private function generateNomor(int $companyId): string
    {
        $year = now()->format('Y');
        $month = now()->format('n');
        $monthCode = $this->monthCode((int) $month);

        $last = $this->companyPenawarans($companyId)
            ->whereYear('tanggal', $year)
            ->count();

        return sprintf('PNW/%04d/%s/%s', $last + 1, $monthCode, $year);
    }

    private function resolveToAddress(int $companyId, string $toCompany, ?string $fallbackAddress): string
    {
        $resolvedAddress = Customer::where('company_id', $companyId)
            ->where('nama', $toCompany)
            ->value('alamat');

        $resolvedAddress = $resolvedAddress ?: $fallbackAddress;

        if (! $resolvedAddress) {
            throw ValidationException::withMessages([
                'to_company' => 'Alamat customer tidak ditemukan.',
            ]);
        }

        return $resolvedAddress;
    }

    private function calculateItems(array $items): array
    {
        $subtotal = 0;
        $normalizedItems = [];

        foreach ($items as $item) {
            $qty = (float) $item['qty'];
            $unitPrice = (float) $item['unit_price'];
            $amount = $qty * $unitPrice;
            $subtotal += $amount;

            $normalizedItems[] = [
                'nama' => $item['nama'],
                'rincian' => $item['rincian'] ?? null,
                'qty' => $qty,
                'satuan' => $item['satuan'],
                'unit_price' => $unitPrice,
                'amount' => $amount,
            ];
        }

        return [
            'items' => $normalizedItems,
            'subtotal' => $subtotal,
        ];
    }

    private function penawaranMeta(int $companyId): array
    {
        $customers = Customer::where('company_id', $companyId)
            ->orderBy('nama')
            ->get(['id', 'nama', 'alamat', 'no_hp', 'email']);

        $mitras = Mitra::where('company_id', $companyId)
            ->orderBy('nama')
            ->get([
                'id',
                'nama',
                'email',
                'alamat',
                'nomor_penawaran',
                'nomor_invoice',
                'nomor_surat_jalan',
                'nomor_berita_acara',
            ]);

        $companyNames = $this->companyPenawarans($companyId)
            ->whereNotNull('to_company')
            ->where('to_company', '!=', '')
            ->select('to_company')
            ->distinct()
            ->orderBy('to_company')
            ->pluck('to_company');

        return [
            'nomor_preview' => $this->generateNomor($companyId),
            'to_company_options' => $customers->pluck('nama')
                ->merge($companyNames)
                ->filter()
                ->unique()
                ->sort()
                ->values(),
            'customers' => $customers,
            'mitras' => $mitras,
            'defaults' => [
                'tanggal' => now()->format('Y-m-d'),
                'tax_percent' => 11,
                'status' => 'draft',
                'jenis_kontrak' => 'satuan',
                'signature_role' => 'Direktur',
                'keterangan' => "1. Masa berlaku penawaran 7 Hari\n2. Garansi produk selama 1 Tahun\n3. Harga sudah termasuk pajak 11%",
            ],
            'options' => [
                'jenis_kontrak' => ['kontrak', 'satuan'],
                'signature_role' => ['Direktur', 'Manager', 'Sales'],
                'satuan' => ['month', 'pcs', 'item', 'unit'],
                'status' => ['draft', 'submitted', 'approved', 'rejected'],
            ],
        ];
    }

    private function validateCreatePayload(Request $request, int $companyId): array
    {
        $validated = $request->validate([
            'mitra_id' => [
                'nullable',
                'integer',
                Rule::exists('mitras', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'tanggal' => ['required', 'date'],
            'to_company' => ['required', 'string', 'max:255'],
            'to_address' => ['nullable', 'string', 'max:500'],
            'jenis_kontrak' => ['required', Rule::in(['kontrak', 'satuan'])],
            'signature_role' => ['required', Rule::in(['Direktur', 'Manager', 'Sales'])],
            'keterangan' => ['nullable', 'string'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['nullable', Rule::in(['draft', 'submitted'])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.nama' => ['required', 'string', 'max:255'],
            'items.*.rincian' => ['nullable', 'string'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.satuan' => ['required', Rule::in(['month', 'pcs', 'item', 'unit'])],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $validated['mitra_id'] = isset($validated['mitra_id']) ? (int) $validated['mitra_id'] : null;

        return $validated;
    }

    private function validateUpdatePayload(Request $request): array
    {
        return $request->validate([
            'tanggal' => ['required', 'date'],
            'to_company' => ['required', 'string', 'max:255'],
            'to_address' => ['nullable', 'string', 'max:500'],
            'jenis_kontrak' => ['required', Rule::in(['kontrak', 'satuan'])],
            'signature_role' => ['required', Rule::in(['Direktur', 'Manager', 'Sales'])],
            'keterangan' => ['nullable', 'string'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['nullable', Rule::in(['draft', 'submitted', 'approved', 'rejected'])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.nama' => ['required', 'string', 'max:255'],
            'items.*.rincian' => ['nullable', 'string'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.satuan' => ['required', Rule::in(['month', 'pcs', 'item', 'unit'])],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);
    }

    private function resolveNomorForCreate(int $companyId, ?int $mitraId): string
    {
        $nomor = null;

        if ($mitraId) {
            $nomor = Mitra::where('company_id', $companyId)
                ->where('id', $mitraId)
                ->value('nomor_penawaran');
        }

        $nomor = $nomor ?: $this->generateNomor($companyId);

        if (Penawaran::where('nomor', $nomor)->exists()) {
            throw ValidationException::withMessages([
                'mitra_id' => 'Nomor penawaran untuk mitra ini sudah digunakan.',
            ]);
        }

        return $nomor;
    }

    public function meta(): JsonResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();

        return response()->json($this->penawaranMeta($companyId));
    }

    public function index(): JsonResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();

        $penawarans = Penawaran::where('company_id', $companyId)
            ->with([
                'mitra:id,nama',
                'items',
                'purchasingOrder:id,penawaran_id,dokumen_path,dokumen_name,nomor_po,tanggal_po,uploaded_by,uploaded_at',
                'invoices' => function ($query) {
                    $query->orderByDesc('tanggal')->orderByDesc('id');
                },
                'invoices.fakturPajak:id,invoice_id,dokumen_path,dokumen_name,payment_status,payment_date',
            ])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Penawaran $penawaran) => $this->serializePenawaran($penawaran));

        return response()->json([
            'data' => $penawarans,
        ]);
    }

    public function show(Penawaran $penawaran): JsonResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($penawaran->company_id !== $companyId, 403);

        $this->loadPenawaranRelations($penawaran);

        return response()->json([
            'data' => $this->serializePenawaran($penawaran, true),
        ]);
    }

    public function send(Penawaran $penawaran): JsonResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($penawaran->company_id !== $companyId, 403);

        $customerName = $penawaran->to_company ?? $penawaran->customer_nama;
        $resolvedEmail = Customer::where('company_id', $companyId)
            ->where('nama', $customerName)
            ->value('email');

        if (empty($resolvedEmail)) {
            return response()->json([
                'message' => 'Email customer belum diisi.',
            ], 422);
        }

        $penawaran->load('items');

        $fileName = 'penawaran-' . str_replace('/', '-', $penawaran->nomor) . '.pdf';
        $pdf = Pdf::loadView('penawaran.pdf', compact('penawaran'))
            ->setPaper('a4', 'portrait');
        $pdfData = $pdf->output();

        try {
            Mail::to($resolvedEmail)->send(new PenawaranMail($penawaran, $pdfData, $fileName));
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal mengirim email. Silakan cek konfigurasi email.',
            ], 500);
        }

        return response()->json([
            'message' => 'Surat penawaran berhasil dikirim ke email customer.',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();
        $validated = $this->validateCreatePayload($request, $companyId);
        $calculated = $this->calculateItems($validated['items']);
        $taxPercent = (float) ($validated['tax_percent'] ?? 11);
        $taxAmount = $calculated['subtotal'] * ($taxPercent / 100);
        $total = $calculated['subtotal'] + $taxAmount;
        $resolvedAddress = $this->resolveToAddress($companyId, $validated['to_company'], $validated['to_address'] ?? null);
        $nomor = $this->resolveNomorForCreate($companyId, $validated['mitra_id']);

        $penawaran = DB::transaction(function () use ($companyId, $validated, $calculated, $taxPercent, $taxAmount, $total, $resolvedAddress, $nomor) {
            $penawaran = Penawaran::create([
                'company_id' => $companyId,
                'mitra_id' => $validated['mitra_id'],
                'user_id' => auth()->id(),
                'nomor' => $nomor,
                'tanggal' => $validated['tanggal'],
                'customer_nama' => $validated['to_company'],
                'to_company' => $validated['to_company'],
                'to_address' => $resolvedAddress,
                'jenis_kontrak' => $validated['jenis_kontrak'],
                'signature_role' => $validated['signature_role'],
                'keterangan' => $validated['keterangan'] ?? null,
                'subtotal' => $calculated['subtotal'],
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'status' => $validated['status'] ?? 'draft',
            ]);

            $penawaran->items()->createMany($calculated['items']);

            return $penawaran;
        });

        $this->loadPenawaranRelations($penawaran);

        return response()->json([
            'message' => 'Surat Penawaran berhasil dibuat.',
            'data' => $this->serializePenawaran($penawaran, true),
        ], 201);
    }

    public function update(Request $request, Penawaran $penawaran): JsonResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($penawaran->company_id !== $companyId, 403);

        $validated = $this->validateUpdatePayload($request);
        $calculated = $this->calculateItems($validated['items']);
        $taxPercent = (float) ($validated['tax_percent'] ?? 11);
        $taxAmount = $calculated['subtotal'] * ($taxPercent / 100);
        $total = $calculated['subtotal'] + $taxAmount;
        $resolvedAddress = $this->resolveToAddress($companyId, $validated['to_company'], $validated['to_address'] ?? null);

        DB::transaction(function () use ($penawaran, $validated, $calculated, $taxPercent, $taxAmount, $total, $resolvedAddress) {
            $penawaran->update([
                'tanggal' => $validated['tanggal'],
                'customer_nama' => $validated['to_company'],
                'to_company' => $validated['to_company'],
                'to_address' => $resolvedAddress,
                'jenis_kontrak' => $validated['jenis_kontrak'],
                'signature_role' => $validated['signature_role'],
                'keterangan' => $validated['keterangan'] ?? null,
                'subtotal' => $calculated['subtotal'],
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'status' => $validated['status'] ?? $penawaran->status,
            ]);

            $penawaran->items()->delete();
            $penawaran->items()->createMany($calculated['items']);
        });

        $this->loadPenawaranRelations($penawaran);

        return response()->json([
            'message' => 'Surat Penawaran berhasil diperbarui.',
            'data' => $this->serializePenawaran($penawaran, true),
        ]);
    }

    public function destroy(Penawaran $penawaran): JsonResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($penawaran->company_id !== $companyId, 403);

        $penawaran->delete();

        return response()->json([
            'message' => 'Surat Penawaran berhasil dihapus.',
        ]);
    }

    private function serializePenawaran(Penawaran $penawaran, bool $includeInvoices = false): array
    {
        $latestInvoice = $penawaran->invoices->first();

        return [
            'id' => $penawaran->id,
            'company_id' => $penawaran->company_id,
            'mitra_id' => $penawaran->mitra_id,
            'user_id' => $penawaran->user_id,
            'nomor' => $penawaran->nomor,
            'tanggal' => $penawaran->tanggal,
            'customer_nama' => $penawaran->customer_nama,
            'to_company' => $penawaran->to_company,
            'to_address' => $penawaran->to_address,
            'jenis_kontrak' => $penawaran->jenis_kontrak,
            'signature_role' => $penawaran->signature_role,
            'keterangan' => $penawaran->keterangan,
            'subtotal' => (float) $penawaran->subtotal,
            'tax_percent' => (float) $penawaran->tax_percent,
            'tax_amount' => (float) $penawaran->tax_amount,
            'total' => (float) $penawaran->total,
            'status' => $penawaran->status,
            'invoice_date' => $penawaran->invoice_date,
            'invoice_number' => $penawaran->invoice_number,
            'invoice_sequence' => $penawaran->invoice_sequence,
            'approved_by' => $penawaran->approved_by,
            'approved_at' => $penawaran->approved_at,
            'company' => $penawaran->relationLoaded('company') && $penawaran->company ? [
                'id' => $penawaran->company->id,
                'name' => $penawaran->company->name,
                'address' => $penawaran->company->address,
                'logo' => $penawaran->company->logo,
            ] : null,
            'user' => $penawaran->relationLoaded('user') && $penawaran->user ? [
                'id' => $penawaran->user->id,
                'name' => $penawaran->user->name,
                'email' => $penawaran->user->email,
            ] : null,
            'approver' => $penawaran->relationLoaded('approver') && $penawaran->approver ? [
                'id' => $penawaran->approver->id,
                'name' => $penawaran->approver->name,
                'email' => $penawaran->approver->email,
            ] : null,
            'mitra' => $penawaran->relationLoaded('mitra') && $penawaran->mitra ? [
                'id' => $penawaran->mitra->id,
                'nama' => $penawaran->mitra->nama,
                'email' => $penawaran->mitra->email,
                'alamat' => $penawaran->mitra->alamat,
                'nomor_penawaran' => $penawaran->mitra->nomor_penawaran,
                'nomor_invoice' => $penawaran->mitra->nomor_invoice,
                'nomor_surat_jalan' => $penawaran->mitra->nomor_surat_jalan,
                'nomor_berita_acara' => $penawaran->mitra->nomor_berita_acara,
            ] : null,
            'items' => $penawaran->items->map(fn ($item) => [
                'id' => $item->id,
                'nama' => $item->nama,
                'rincian' => $item->rincian,
                'qty' => (float) $item->qty,
                'satuan' => $item->satuan,
                'unit_price' => (float) $item->unit_price,
                'amount' => (float) $item->amount,
            ]),
            'purchasing_order' => $penawaran->purchasingOrder ? [
                'id' => $penawaran->purchasingOrder->id,
                'dokumen_path' => $penawaran->purchasingOrder->dokumen_path,
                'dokumen_name' => $penawaran->purchasingOrder->dokumen_name,
                'nomor_po' => $penawaran->purchasingOrder->nomor_po,
                'tanggal_po' => $penawaran->purchasingOrder->tanggal_po,
                'uploaded_by' => $penawaran->purchasingOrder->uploaded_by,
                'uploaded_at' => $penawaran->purchasingOrder->uploaded_at,
            ] : null,
            'latest_invoice' => $latestInvoice ? $this->serializeInvoice($latestInvoice) : null,
            'invoices' => $includeInvoices
                ? $penawaran->invoices->map(fn ($invoice) => $this->serializeInvoice($invoice, true))
                : null,
        ];
    }

    private function serializeInvoice($invoice, bool $includeDocuments = false): array
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
            'faktur_pajak' => $includeDocuments && $invoice->relationLoaded('fakturPajak') && $invoice->fakturPajak ? [
                'id' => $invoice->fakturPajak->id,
                'dokumen_path' => $invoice->fakturPajak->dokumen_path,
                'dokumen_name' => $invoice->fakturPajak->dokumen_name,
                'payment_status' => $invoice->fakturPajak->payment_status,
                'payment_date' => $invoice->fakturPajak->payment_date,
            ] : null,
            'surat_jalan' => $includeDocuments && $invoice->relationLoaded('suratJalan') && $invoice->suratJalan ? [
                'id' => $invoice->suratJalan->id,
                'nomor' => $invoice->suratJalan->nomor,
                'tanggal' => $invoice->suratJalan->tanggal,
                'pemberi_nama' => $invoice->suratJalan->pemberi_nama,
                'pemberi_jabatan' => $invoice->suratJalan->pemberi_jabatan,
                'pemberi_alamat' => $invoice->suratJalan->pemberi_alamat,
                'penerima_nama' => $invoice->suratJalan->penerima_nama,
                'penerima_hp' => $invoice->suratJalan->penerima_hp,
                'kota_tanggal_manual' => $invoice->suratJalan->kota_tanggal_manual,
            ] : null,
            'berita_acara' => $includeDocuments && $invoice->relationLoaded('beritaAcara') && $invoice->beritaAcara ? [
                'id' => $invoice->beritaAcara->id,
                'nomor' => $invoice->beritaAcara->nomor,
                'tanggal' => $invoice->beritaAcara->tanggal,
                'perihal' => $invoice->beritaAcara->perihal,
                'keterangan_akhir' => $invoice->beritaAcara->keterangan_akhir,
                'kota_tanggal_manual' => $invoice->beritaAcara->kota_tanggal_manual,
            ] : null,
        ];
    }
}
