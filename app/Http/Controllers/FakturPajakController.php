<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCompanyId;
use App\Models\FakturPajak;
use App\Models\Invoice;
use Illuminate\Http\Request;

class FakturPajakController extends Controller
{
    use ResolvesCompanyId;

    public function index()
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $invoices = Invoice::whereHas('penawaran', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
            ->with(['penawaran', 'fakturPajak'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        return view('faktur-pajak.index', compact('invoices'));
    }

    public function store(Request $request, Invoice $invoice)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $invoice->load('penawaran');
        abort_if(!$invoice->penawaran || $invoice->penawaran->company_id !== $companyId, 403);

        $validated = $request->validate([
            'dokumen' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $file = $validated['dokumen'];
        $path = $file->store('faktur-pajak', 'public');

        FakturPajak::updateOrCreate(
            ['invoice_id' => $invoice->id],
            [
                'dokumen_path' => $path,
                'dokumen_name' => $file->getClientOriginalName(),
                'uploaded_by' => auth()->id(),
                'uploaded_at' => now(),
            ]
        );

        return redirect()->route('faktur-pajak.index')
            ->with('success', 'Dokumen Faktur Pajak berhasil diupload.');
    }

    public function verifyPayment(Request $request, Invoice $invoice)
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $invoice->load('penawaran', 'fakturPajak');
        abort_if(!$invoice->penawaran || $invoice->penawaran->company_id !== $companyId, 403);
        abort_if(!$invoice->fakturPajak, 403, 'Dokumen faktur pajak belum diupload.');

        $validated = $request->validate([
            'payment_date' => ['required', 'date'],
        ]);

        $invoice->fakturPajak->update([
            'payment_status' => 'paid',
            'payment_date' => $validated['payment_date'],
        ]);

        return redirect()->route('faktur-pajak.index')
            ->with('success', 'Status pembayaran faktur pajak berhasil diubah menjadi sudah dibayarkan.');
    }

}
