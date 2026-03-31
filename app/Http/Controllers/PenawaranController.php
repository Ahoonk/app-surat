<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Concerns\ResolvesCompanyId;
use App\Models\Penawaran;
use App\Models\Customer;
use App\Mail\PenawaranMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;

class PenawaranController extends Controller
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

        $penawarans = $this->companyPenawarans($companyId)
            ->latest()
            ->get();

        return view('penawaran.index', compact('penawarans'));
    }

    public function create()
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $nomorPreview = $this->generateNomor($companyId);
        $toCompanyOptions = $this->companyPenawarans($companyId)
            ->whereNotNull('to_company')
            ->where('to_company', '!=', '')
            ->select('to_company')
            ->distinct()
            ->orderBy('to_company')
            ->pluck('to_company');

        $customers = Customer::where('company_id', $companyId)
            ->orderBy('nama')
            ->get();

        return view('penawaran.create', compact('nomorPreview', 'toCompanyOptions', 'customers'));
    }

    public function show(Penawaran $penawaran)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        abort_if($penawaran->company_id !== $companyId, 403);

        $penawaran->load('items');

        return view('penawaran.show', compact('penawaran'));
    }

    public function pdf(Penawaran $penawaran)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        abort_if($penawaran->company_id !== $companyId, 403);

        $penawaran->load('items');

        $fileName = 'penawaran-' . str_replace('/', '-', $penawaran->nomor) . '.pdf';
        $pdf = Pdf::loadView('penawaran.pdf', compact('penawaran'))
            ->setPaper('a4', 'portrait');

        if (request()->boolean('download')) {
            return $pdf->download($fileName);
        }

        return $pdf->stream($fileName);
    }

    public function send(Penawaran $penawaran)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        abort_if($penawaran->company_id !== $companyId, 403);

        $customerName = $penawaran->to_company ?? $penawaran->customer_nama;
        $resolvedEmail = Customer::where('company_id', $companyId)
            ->where('nama', $customerName)
            ->value('email');

        if (empty($resolvedEmail)) {
            return back()->with('error', 'Email customer belum diisi.');
        }

        $penawaran->load('items');

        $fileName = 'penawaran-' . str_replace('/', '-', $penawaran->nomor) . '.pdf';
        $pdf = Pdf::loadView('penawaran.pdf', compact('penawaran'))
            ->setPaper('a4', 'portrait');
        $pdfData = $pdf->output();

        try {
            Mail::to($resolvedEmail)->send(new PenawaranMail($penawaran, $pdfData, $fileName));
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengirim email. Silakan cek konfigurasi email.');
        }

        return back()->with('success', 'Surat penawaran berhasil dikirim ke email customer.');
    }

    private function generateNomor(int $companyId): string
    {
        $year = now()->format('Y');
        $month = now()->format('n');
        $monthCode = $this->monthCode($month);

        $last = $this->companyPenawarans($companyId)
            ->whereYear('tanggal', $year)
            ->count();

        $next = $last + 1;

        return sprintf("PNW/%04d/%s/%s", $next, $monthCode, $year);
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

    public function store(Request $request)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'to_company' => ['required', 'string', 'max:255'],
            'to_address' => ['nullable', 'string', 'max:500'],
            'jenis_kontrak' => ['required', 'in:kontrak,satuan'],
            'signature_role' => ['required', 'in:Direktur,Manager,Sales'],
            'keterangan' => ['nullable', 'string'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['nullable', 'in:draft,submitted'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.nama' => ['required', 'string', 'max:255'],
            'items.*.rincian' => ['nullable', 'string'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.satuan' => ['required', 'in:month,pcs,item,unit'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $taxPercent = (float) ($validated['tax_percent'] ?? 11);
        $subtotal = 0;
        $items = [];

        $resolvedAddress = Customer::where('company_id', $companyId)
            ->where('nama', $validated['to_company'])
            ->value('alamat');
        $resolvedAddress = $resolvedAddress ?: ($validated['to_address'] ?? null);

        if (! $resolvedAddress) {
            return back()
                ->withErrors(['to_company' => 'Alamat customer tidak ditemukan.'])
                ->withInput();
        }

        foreach ($validated['items'] as $item) {
            $qty = (float) $item['qty'];
            $unitPrice = (float) $item['unit_price'];
            $amount = $qty * $unitPrice;
            $subtotal += $amount;

            $items[] = [
                'nama' => $item['nama'],
                'rincian' => $item['rincian'] ?? null,
                'qty' => $qty,
                'satuan' => $item['satuan'],
                'unit_price' => $unitPrice,
                'amount' => $amount,
            ];
        }

        $taxAmount = $subtotal * ($taxPercent / 100);
        $total = $subtotal + $taxAmount;

        $penawaran = DB::transaction(function () use ($companyId, $validated, $taxPercent, $subtotal, $taxAmount, $total, $items, $resolvedAddress) {
            $nomor = $this->generateNomor($companyId);

            $penawaran = Penawaran::create([
                'company_id' => $companyId,
                'user_id' => auth()->id(),
                'nomor' => $nomor,
                'tanggal' => $validated['tanggal'],
                'customer_nama' => $validated['to_company'],
                'to_company' => $validated['to_company'],
                'to_address' => $resolvedAddress,
                'jenis_kontrak' => $validated['jenis_kontrak'],
                'signature_role' => $validated['signature_role'],
                'keterangan' => $validated['keterangan'] ?? null,
                'subtotal' => $subtotal,
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'status' => $validated['status'] ?? 'draft',
            ]);

            $penawaran->items()->createMany($items);

            return $penawaran;
        });

        return redirect()->route('penawaran.show', $penawaran)
            ->with('success', 'Surat Penawaran berhasil dibuat.');
    }

    public function edit(Penawaran $penawaran)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        abort_if($penawaran->company_id !== $companyId, 403);
        $penawaran->load('items');

        $toCompanyOptions = $this->companyPenawarans($companyId)
            ->whereNotNull('to_company')
            ->where('to_company', '!=', '')
            ->select('to_company')
            ->distinct()
            ->orderBy('to_company')
            ->pluck('to_company');

        $customers = Customer::where('company_id', $companyId)
            ->orderBy('nama')
            ->get();

        return view('penawaran.edit', compact('penawaran', 'toCompanyOptions', 'customers'));
    }

    public function update(Request $request, Penawaran $penawaran)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        abort_if($penawaran->company_id !== $companyId, 403);

        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'to_company' => ['required', 'string', 'max:255'],
            'to_address' => ['nullable', 'string', 'max:500'],
            'jenis_kontrak' => ['required', 'in:kontrak,satuan'],
            'signature_role' => ['required', 'in:Direktur,Manager,Sales'],
            'keterangan' => ['nullable', 'string'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['nullable', 'in:draft,submitted,approved,rejected'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.nama' => ['required', 'string', 'max:255'],
            'items.*.rincian' => ['nullable', 'string'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.satuan' => ['required', 'in:month,pcs,item,unit'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $taxPercent = (float) ($validated['tax_percent'] ?? 11);
        $subtotal = 0;
        $items = [];

        $resolvedAddress = Customer::where('company_id', $companyId)
            ->where('nama', $validated['to_company'])
            ->value('alamat');
        $resolvedAddress = $resolvedAddress ?: ($validated['to_address'] ?? null);

        if (! $resolvedAddress) {
            return back()
                ->withErrors(['to_company' => 'Alamat customer tidak ditemukan.'])
                ->withInput();
        }

        foreach ($validated['items'] as $item) {
            $qty = (float) $item['qty'];
            $unitPrice = (float) $item['unit_price'];
            $amount = $qty * $unitPrice;
            $subtotal += $amount;

            $items[] = [
                'nama' => $item['nama'],
                'rincian' => $item['rincian'] ?? null,
                'qty' => $qty,
                'satuan' => $item['satuan'],
                'unit_price' => $unitPrice,
                'amount' => $amount,
            ];
        }

        $taxAmount = $subtotal * ($taxPercent / 100);
        $total = $subtotal + $taxAmount;

        DB::transaction(function () use ($penawaran, $validated, $taxPercent, $subtotal, $taxAmount, $total, $items, $resolvedAddress) {
            $penawaran->update([
                'tanggal' => $validated['tanggal'],
                'customer_nama' => $validated['to_company'],
                'to_company' => $validated['to_company'],
                'to_address' => $resolvedAddress,
                'jenis_kontrak' => $validated['jenis_kontrak'],
                'signature_role' => $validated['signature_role'],
                'keterangan' => $validated['keterangan'] ?? null,
                'subtotal' => $subtotal,
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'status' => $validated['status'] ?? $penawaran->status,
            ]);

            $penawaran->items()->delete();
            $penawaran->items()->createMany($items);
        });

        return redirect()->route('penawaran.index')
            ->with('success', 'Surat Penawaran berhasil diperbarui.');
    }

    public function destroy(Penawaran $penawaran)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        abort_if($penawaran->company_id !== $companyId, 403);

        $penawaran->delete();

        return redirect()->route('penawaran.index')
            ->with('success', 'Surat Penawaran berhasil dihapus.');
    }

    public function approveForInvoice(Request $request, Penawaran $penawaran)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        abort_if($penawaran->company_id !== $companyId, 403);

        $penawaran->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('purchasing-order.index')
            ->with('success', 'Penawaran disetujui. Lanjutkan upload dokumen di menu Purchasing Order.');
    }
}
