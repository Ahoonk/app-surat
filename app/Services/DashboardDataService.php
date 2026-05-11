<?php

namespace App\Services;

use App\Models\FakturPajak;
use App\Models\Invoice;
use App\Models\NotaToko;
use App\Models\Penawaran;

class DashboardDataService
{
    public function forCompany(int $companyId): array
    {
        $penawaranQuery = Penawaran::where('company_id', $companyId);
        $approvedWithoutPo = (clone $penawaranQuery)
            ->where('status', 'approved')
            ->whereDoesntHave('purchasingOrder')
            ->count();
        $poUploaded = (clone $penawaranQuery)->whereHas('purchasingOrder')->count();

        $invoiceQuery = $this->companyInvoiceQuery($companyId);

        $invoiceUnpaid = (clone $invoiceQuery)->where('payment_status', 'unpaid')->count();
        $invoicePaid = (clone $invoiceQuery)->where('payment_status', 'paid')->count();

        $invoiceTotalAll = (clone $invoiceQuery)->sum('total');
        $invoiceTotalPaid = (clone $invoiceQuery)
            ->where('payment_status', 'paid')
            ->sum('total');
        $invoiceTotalUnpaid = (clone $invoiceQuery)
            ->where('payment_status', 'unpaid')
            ->sum('total');

        $invoiceTaxRows = (clone $invoiceQuery)
            ->with(['penawaran:id,tax_amount']);
        $invoiceTaxTotalAll = $invoiceTaxRows->get()->sum(function ($invoice) {
            return (float) ($invoice->penawaran?->tax_amount ?? 0);
        });
        $invoiceTaxTotalPaid = (clone $invoiceQuery)
            ->where('payment_status', 'paid')
            ->with(['penawaran:id,tax_amount'])
            ->get()
            ->sum(function ($invoice) {
                return (float) ($invoice->penawaran?->tax_amount ?? 0);
            });
        $invoiceTaxTotalUnpaid = (clone $invoiceQuery)
            ->where('payment_status', 'unpaid')
            ->with(['penawaran:id,tax_amount'])
            ->get()
            ->sum(function ($invoice) {
                return (float) ($invoice->penawaran?->tax_amount ?? 0);
            });

        $fakturQuery = FakturPajak::whereHas('invoice.penawaran', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        });

        $fakturUnpaid = (clone $fakturQuery)->where('payment_status', 'unpaid')->count();
        $fakturPaid = (clone $fakturQuery)->where('payment_status', 'paid')->count();
        $fakturPendingUpload = (clone $invoiceQuery)->whereDoesntHave('fakturPajak')->count();

        $notaTokoQuery = $this->companyNotaTokoQuery($companyId);
        $notaTokoUnpaid = (clone $notaTokoQuery)->where('payment_status', 'unpaid')->count();
        $notaTokoPaid = (clone $notaTokoQuery)->where('payment_status', 'paid')->count();
        $notaTokoTotalAll = (clone $notaTokoQuery)->sum('total');
        $notaTokoTotalPaid = (clone $notaTokoQuery)
            ->where('payment_status', 'paid')
            ->sum('total');
        $notaTokoTotalUnpaid = (clone $notaTokoQuery)
            ->where('payment_status', 'unpaid')
            ->sum('total');

        $dashboardStatus = [
            'penawaran' => [
                'draft' => (clone $penawaranQuery)->where('status', 'draft')->count(),
                'submitted' => (clone $penawaranQuery)->where('status', 'submitted')->count(),
                'approved' => (clone $penawaranQuery)->where('status', 'approved')->count(),
                'rejected' => (clone $penawaranQuery)->where('status', 'rejected')->count(),
            ],
            'purchasing_order' => [
                'menunggu_upload' => $approvedWithoutPo,
                'sudah_upload' => $poUploaded,
            ],
            'invoice' => [
                'belum_dibayar' => $invoiceUnpaid,
                'sudah_dibayar' => $invoicePaid,
            ],
            'faktur_pajak' => [
                'menunggu_upload' => $fakturPendingUpload,
                'belum_dibayar' => $fakturUnpaid,
                'sudah_dibayar' => $fakturPaid,
            ],
        ];

        $dashboardFinancial = [
            'total_semua' => $invoiceTotalAll,
            'total_sudah_dibayar' => $invoiceTotalPaid,
            'total_belum_dibayar' => $invoiceTotalUnpaid,
            'jumlah_semua' => $invoiceUnpaid + $invoicePaid,
            'jumlah_sudah_dibayar' => $invoicePaid,
            'jumlah_belum_dibayar' => $invoiceUnpaid,
        ];

        $dashboardTax = [
            'total_semua' => $invoiceTaxTotalAll,
            'total_sudah_dibayar' => $invoiceTaxTotalPaid,
            'total_belum_dibayar' => $invoiceTaxTotalUnpaid,
            'jumlah_semua' => $invoiceUnpaid + $invoicePaid,
            'jumlah_sudah_dibayar' => $invoicePaid,
            'jumlah_belum_dibayar' => $invoiceUnpaid,
        ];

        $dashboardNotaToko = [
            'total_semua' => $notaTokoTotalAll,
            'total_sudah_dibayar' => $notaTokoTotalPaid,
            'total_belum_dibayar' => $notaTokoTotalUnpaid,
            'jumlah_semua' => $notaTokoUnpaid + $notaTokoPaid,
            'jumlah_sudah_dibayar' => $notaTokoPaid,
            'jumlah_belum_dibayar' => $notaTokoUnpaid,
        ];

        $dashboardTransactions = $penawaranQuery
            ->with([
                'items',
                'purchasingOrder',
                'invoices' => function ($query) {
                    $query->orderByDesc('tanggal')->orderByDesc('id');
                },
                'invoices.fakturPajak',
            ])
            ->latest('tanggal')
            ->limit(40)
            ->get()
            ->map(function ($penawaran) {
                $latestInvoice = $penawaran->invoices->first();

                return [
                    'sort_date' => $penawaran->tanggal,
                    'invoice' => $latestInvoice,
                    'penawaran' => $penawaran,
                    'faktur_pajak' => $latestInvoice?->fakturPajak,
                ];
            });

        return compact(
            'dashboardFinancial',
            'dashboardStatus',
            'dashboardTax',
            'dashboardNotaToko',
            'dashboardTransactions'
        );
    }

    private function companyInvoiceQuery(int $companyId)
    {
        return Invoice::whereHas('penawaran', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        });
    }

    private function companyNotaTokoQuery(int $companyId)
    {
        return NotaToko::where('company_id', $companyId);
    }
}
