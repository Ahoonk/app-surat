<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCompanyId;
use App\Models\Invoice;
use App\Models\SuratJalan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    use ResolvesCompanyId;

    private function companyInvoices(int $companyId): Builder
    {
        return Invoice::whereHas('penawaran', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        });
    }

    public function index()
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $invoices = $this->companyInvoices($companyId)
            ->with('penawaran')
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        return view('invoice.index', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $invoice->load('penawaran.items', 'purchasingOrder');
        $penawaran = $invoice->penawaran;
        abort_if(!$penawaran || $penawaran->company_id !== $companyId, 403);

        return view('invoice.show', compact('invoice', 'penawaran'));
    }

    public function pdf(Invoice $invoice)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $invoice->load('penawaran.items', 'purchasingOrder');
        $penawaran = $invoice->penawaran;
        abort_if(!$penawaran || $penawaran->company_id !== $companyId, 403);

        $fileName = 'invoice-' . str_replace('/', '-', $invoice->nomor) . '.pdf';
        $pdf = Pdf::loadView('invoice.pdf', compact('invoice', 'penawaran'))
            ->setPaper('legal', 'portrait');

        if (request()->boolean('download')) {
            return $pdf->download($fileName);
        }

        return $pdf->stream($fileName);
    }

    public function verifyPayment(Request $request, Invoice $invoice)
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $invoice->load('penawaran');
        abort_if(!$invoice->penawaran || $invoice->penawaran->company_id !== $companyId, 403);

        $validated = $request->validate([
            'payment_date' => ['required', 'date'],
        ]);

        $invoice->update([
            'payment_status' => 'paid',
            'payment_date' => $validated['payment_date'],
        ]);

        return redirect()->route('invoice.index')
            ->with('success', 'Status pembayaran invoice berhasil diubah menjadi sudah dibayarkan.');
    }

    public function updatePrintDate(Request $request, Invoice $invoice)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $invoice->load('penawaran');
        abort_if(!$invoice->penawaran || $invoice->penawaran->company_id !== $companyId, 403);

        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
        ]);

        $newNomor = $this->rebuildInvoiceNumberWithDate($invoice->nomor, $validated['tanggal']);

        $invoice->update([
            'tanggal' => $validated['tanggal'],
            'nomor' => $newNomor,
        ]);

        SuratJalan::where('invoice_id', $invoice->id)->update([
            'tanggal' => $validated['tanggal'],
            'nomor' => preg_replace('/^INV\//', 'SJ/', $newNomor),
        ]);

        return redirect()->route('invoice.index')
            ->with('success', 'Tanggal cetak invoice berhasil diperbarui.');
    }

    public function destroy(Invoice $invoice)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $invoice->load('penawaran.purchasingOrder');
        abort_if(!$invoice->penawaran || $invoice->penawaran->company_id !== $companyId, 403);
        abort_if($invoice->penawaran->status !== 'draft', 403, 'Invoice hanya bisa dihapus saat status penawaran draft.');

        $penawaran = $invoice->penawaran;
        $purchasingOrder = $penawaran->purchasingOrder;

        DB::transaction(function () use ($penawaran, $purchasingOrder) {
            // Cancel transaksi: hapus seluruh invoice turunan penawaran ini.
            $penawaran->invoices()->delete();

            // Hapus dokumen PO lama agar transaksi benar-benar bersih.
            if ($purchasingOrder) {
                if ($purchasingOrder->dokumen_path) {
                    Storage::disk('public')->delete($purchasingOrder->dokumen_path);
                }

                $purchasingOrder->delete();
            }

            $penawaran->update([
                'invoice_date' => null,
                'invoice_sequence' => null,
                'invoice_number' => null,
            ]);
        });

        return back()->with('success', 'Transaksi yang dibatalkan berhasil dihapus, termasuk dokumen PO.');
    }

    private function rebuildInvoiceNumberWithDate(string $currentNumber, string $newDate): string
    {
        $date = \Illuminate\Support\Carbon::parse($newDate);
        $running = '001';

        if (preg_match('/^INV\/\d{4}\/\d{2}\/(\d{3})-ASK$/', $currentNumber, $match)) {
            $running = $match[1];
        }

        return sprintf('INV/%s/%s/%s-ASK', $date->format('Y'), $date->format('m'), $running);
    }
}
