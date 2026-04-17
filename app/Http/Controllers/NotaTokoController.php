<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCompanyId;
use App\Mail\NotaTokoMail;
use App\Models\Customer;
use App\Models\NotaToko;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class NotaTokoController extends Controller
{
    use ResolvesCompanyId;

    private function companyNotaTokos(int $companyId): Builder
    {
        return NotaToko::where('company_id', $companyId);
    }

    public function index()
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $notaTokos = $this->companyNotaTokos($companyId)->latest()->get();

        return view('nota-toko.index', compact('notaTokos'));
    }

    public function create()
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $nomorPreview = $this->generateNomor($companyId);

        $customers = Customer::where('company_id', $companyId)
            ->orderBy('nama')
            ->get();

        return view('nota-toko.create', compact('nomorPreview', 'customers'));
    }

    public function store(Request $request)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'customer_nama' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'keterangan' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.nama' => ['required', 'string', 'max:255'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.satuan' => ['required', 'in:month,pcs,item,unit'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $taxPercent = 0;
        $subtotal = 0;
        $items = [];

        $resolvedCustomer = Customer::where('company_id', $companyId)
            ->where('nama', $validated['customer_nama'])
            ->first();
        $resolvedAddress = $resolvedCustomer?->alamat ?? ($validated['alamat'] ?? null);
        $resolvedEmail = $resolvedCustomer?->email ?? ($validated['customer_email'] ?? null);

        if (! $resolvedAddress) {
            return back()
                ->withErrors(['customer_nama' => 'Alamat customer tidak ditemukan.'])
                ->withInput();
        }

        if (! $resolvedEmail) {
            return back()
                ->withErrors(['customer_nama' => 'Email customer tidak ditemukan.'])
                ->withInput();
        }

        foreach ($validated['items'] as $item) {
            $qty = (float) $item['qty'];
            $unitPrice = (float) $item['unit_price'];
            $amount = $qty * $unitPrice;
            $subtotal += $amount;

            $items[] = [
                'nama' => $item['nama'],
                'qty' => $qty,
                'satuan' => $item['satuan'],
                'unit_price' => $unitPrice,
                'amount' => $amount,
            ];
        }

        $taxAmount = 0;
        $total = $subtotal;

        $notaToko = DB::transaction(function () use ($companyId, $validated, $taxPercent, $subtotal, $taxAmount, $total, $items, $resolvedAddress, $resolvedEmail) {
            $notaToko = NotaToko::create([
                'company_id' => $companyId,
                'user_id' => auth()->id(),
                'nomor' => $this->generateNomor($companyId),
                'tanggal' => $validated['tanggal'],
                'customer_nama' => $validated['customer_nama'],
                'customer_email' => $resolvedEmail,
                'alamat' => $resolvedAddress,
                'keterangan' => $validated['keterangan'] ?? null,
                'subtotal' => $subtotal,
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'payment_status' => 'unpaid',
                'payment_date' => null,
            ]);

            $notaToko->items()->createMany($items);

            return $notaToko;
        });

        return redirect()->route('nota-toko.show', $notaToko)->with('success', 'Nota toko berhasil dibuat.');
    }

    public function show(NotaToko $notaToko)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        abort_if($notaToko->company_id !== $companyId, 403);

        $notaToko->load('items');

        return view('nota-toko.show', compact('notaToko'));
    }

    public function edit(NotaToko $notaToko)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        abort_if($notaToko->company_id !== $companyId, 403);
        $notaToko->load('items');

        $customers = Customer::where('company_id', $companyId)
            ->orderBy('nama')
            ->get();

        return view('nota-toko.edit', compact('notaToko', 'customers'));
    }

    public function update(Request $request, NotaToko $notaToko)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        abort_if($notaToko->company_id !== $companyId, 403);

        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'customer_nama' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'keterangan' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.nama' => ['required', 'string', 'max:255'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.satuan' => ['required', 'in:month,pcs,item,unit'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $taxPercent = 0;
        $subtotal = 0;
        $items = [];

        $resolvedCustomer = Customer::where('company_id', $companyId)
            ->where('nama', $validated['customer_nama'])
            ->first();
        $resolvedAddress = $resolvedCustomer?->alamat ?? ($validated['alamat'] ?? null);
        $resolvedEmail = $resolvedCustomer?->email ?? ($validated['customer_email'] ?? null);

        if (! $resolvedAddress) {
            return back()
                ->withErrors(['customer_nama' => 'Alamat customer tidak ditemukan.'])
                ->withInput();
        }

        if (! $resolvedEmail) {
            return back()
                ->withErrors(['customer_nama' => 'Email customer tidak ditemukan.'])
                ->withInput();
        }

        foreach ($validated['items'] as $item) {
            $qty = (float) $item['qty'];
            $unitPrice = (float) $item['unit_price'];
            $amount = $qty * $unitPrice;
            $subtotal += $amount;

            $items[] = [
                'nama' => $item['nama'],
                'qty' => $qty,
                'satuan' => $item['satuan'],
                'unit_price' => $unitPrice,
                'amount' => $amount,
            ];
        }

        $taxAmount = 0;
        $total = $subtotal;

        DB::transaction(function () use ($notaToko, $validated, $taxPercent, $subtotal, $taxAmount, $total, $items, $resolvedAddress, $resolvedEmail) {
            $notaToko->update([
                'tanggal' => $validated['tanggal'],
                'customer_nama' => $validated['customer_nama'],
                'customer_email' => $resolvedEmail,
                'alamat' => $resolvedAddress,
                'keterangan' => $validated['keterangan'] ?? null,
                'subtotal' => $subtotal,
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'total' => $total,
            ]);

            $notaToko->items()->delete();
            $notaToko->items()->createMany($items);
        });

        return redirect()->route('nota-toko.index')->with('success', 'Nota toko berhasil diperbarui.');
    }

    public function verifyPayment(Request $request, NotaToko $notaToko)
    {
        abort_unless(auth()->user()?->role && in_array(auth()->user()->role, ['admin', 'superadmin'], true), 403);

        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        abort_if($notaToko->company_id !== $companyId, 403);

        $validated = $request->validate([
            'payment_date' => ['required', 'date'],
        ]);

        $notaToko->update([
            'payment_status' => 'paid',
            'payment_date' => $validated['payment_date'],
        ]);

        return redirect()->route('nota-toko.index')
            ->with('success', 'Status pembayaran nota toko berhasil diubah menjadi sudah dibayarkan.');
    }

    public function pdf(NotaToko $notaToko)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        abort_if($notaToko->company_id !== $companyId, 403);

        $notaToko->load('items');

        $fileName = 'nota-toko-' . str_replace('/', '-', $notaToko->nomor) . '.pdf';
        $pdf = $this->makeNotaTokoPdf($notaToko);

        if (request()->boolean('download')) {
            return $pdf->download($fileName);
        }

        return $pdf->stream($fileName);
    }

    public function send(NotaToko $notaToko)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        abort_if($notaToko->company_id !== $companyId, 403);

        $resolvedEmail = Customer::where('company_id', $companyId)
            ->where('nama', $notaToko->customer_nama)
            ->value('email');

        if (empty($resolvedEmail)) {
            return back()->with('error', 'Email customer belum diisi.');
        }

        $notaToko->load('items');

        $fileName = 'nota-toko-' . str_replace('/', '-', $notaToko->nomor) . '.pdf';
        $pdf = $this->makeNotaTokoPdf($notaToko);
        $pdfData = $pdf->output();

        try {
            Mail::to($resolvedEmail)->send(new NotaTokoMail($notaToko, $pdfData, $fileName));
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengirim email. Silakan cek konfigurasi email.');
        }

        return back()->with('success', 'Nota toko berhasil dikirim ke email customer.');
    }

    public function destroy(NotaToko $notaToko)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        abort_if($notaToko->company_id !== $companyId, 403);

        $notaToko->delete();

        return redirect()->route('nota-toko.index')->with('success', 'Nota toko berhasil dihapus.');
    }

    private function generateNomor(int $companyId): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');

        $next = $this->companyNotaTokos($companyId)
            ->whereYear('tanggal', $year)
            ->count() + 1;

        return sprintf('NT/%s/%s/%04d', $year, $month, $next);
    }

    private function makeNotaTokoPdf(NotaToko $notaToko)
    {
        $pdfWidthPt = 210 * 2.83465;
        $pdfHeightPt = 150 * 2.83465;

        return Pdf::loadView('nota-toko.pdf', compact('notaToko'))
            ->setPaper([0, 0, $pdfWidthPt, $pdfHeightPt], 'portrait');
    }
}
