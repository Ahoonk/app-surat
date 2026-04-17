<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard
        </h2>
    </x-slot>
    <style>
        .dashboard-detail-table th,
        .dashboard-detail-table td {
            vertical-align: top;
        }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                    <div>
                        <h3 class="text-lg font-bold">
                            Selamat Datang
                        </h3>

                        <p class="mt-1 text-base font-semibold text-gray-700">
                            {{ auth()->user()->name }}
                        </p>
                    </div>

                    <div class="text-left md:text-right">
                        <h3 class="text-2xl font-bold text-gray-900">Laporan Keuangan</h3>
                        <p class="mt-1 text-sm text-gray-500">Tahun 2026</p>
                    </div>
                </div>

                @php
                    $invoiceTotalAll = $dashboardFinancial['total_semua'] ?? 0;
                    $invoiceTotalPaid = $dashboardFinancial['total_sudah_dibayar'] ?? 0;
                    $invoiceTotalUnpaid = $dashboardFinancial['total_belum_dibayar'] ?? 0;
                    $invoiceCountAll = $dashboardFinancial['jumlah_semua'] ?? 0;
                    $invoiceCountPaid = $dashboardFinancial['jumlah_sudah_dibayar'] ?? 0;
                    $invoiceCountUnpaid = $dashboardFinancial['jumlah_belum_dibayar'] ?? 0;
                    $taxTotalAll = $dashboardTax['total_semua'] ?? 0;
                    $taxTotalPaid = $dashboardTax['total_sudah_dibayar'] ?? 0;
                    $taxTotalUnpaid = $dashboardTax['total_belum_dibayar'] ?? 0;
                    $taxCountAll = $dashboardTax['jumlah_semua'] ?? 0;
                    $taxCountPaid = $dashboardTax['jumlah_sudah_dibayar'] ?? 0;
                    $taxCountUnpaid = $dashboardTax['jumlah_belum_dibayar'] ?? 0;
                    $notaTotalAll = $dashboardNotaToko['total_semua'] ?? 0;
                    $notaTotalPaid = $dashboardNotaToko['total_sudah_dibayar'] ?? 0;
                    $notaTotalUnpaid = $dashboardNotaToko['total_belum_dibayar'] ?? 0;
                    $notaCountAll = $dashboardNotaToko['jumlah_semua'] ?? 0;
                    $notaCountPaid = $dashboardNotaToko['jumlah_sudah_dibayar'] ?? 0;
                    $notaCountUnpaid = $dashboardNotaToko['jumlah_belum_dibayar'] ?? 0;
                @endphp

                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-5 shadow-sm">
                        <p class="text-sm text-slate-500">Total Semua Invoice</p>
                        <p class="mt-2 text-2xl font-bold text-slate-900">Rp {{ number_format($invoiceTotalAll, 2, ',', '.') }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $invoiceCountAll }} invoice</p>
                    </div>

                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                        <p class="text-sm text-emerald-700">Total Invoice Sudah Dibayar</p>
                        <p class="mt-2 text-2xl font-bold text-emerald-800">Rp {{ number_format($invoiceTotalPaid, 2, ',', '.') }}</p>
                        <p class="mt-1 text-xs text-emerald-700">{{ $invoiceCountPaid }} invoice</p>
                    </div>

                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                        <p class="text-sm text-amber-700">Total Invoice Belum Dibayar</p>
                        <p class="mt-2 text-2xl font-bold text-amber-800">Rp {{ number_format($invoiceTotalUnpaid, 2, ',', '.') }}</p>
                        <p class="mt-1 text-xs text-amber-700">{{ $invoiceCountUnpaid }} invoice</p>
                    </div>
                </div>

                <div class="mt-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-5 shadow-sm">
                            <p class="text-sm text-slate-500">Total Pajak Semua</p>
                            <p class="mt-2 text-2xl font-bold text-slate-900">Rp {{ number_format($taxTotalAll, 2, ',', '.') }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $taxCountAll }} invoice</p>
                        </div>

                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                            <p class="text-sm text-emerald-700">Total Pajak Sudah Dibayar</p>
                            <p class="mt-2 text-2xl font-bold text-emerald-800">Rp {{ number_format($taxTotalPaid, 2, ',', '.') }}</p>
                            <p class="mt-1 text-xs text-emerald-700">{{ $taxCountPaid }} invoice</p>
                        </div>

                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                            <p class="text-sm text-amber-700">Total Pajak Belum Dibayar</p>
                            <p class="mt-2 text-2xl font-bold text-amber-800">Rp {{ number_format($taxTotalUnpaid, 2, ',', '.') }}</p>
                            <p class="mt-1 text-xs text-amber-700">{{ $taxCountUnpaid }} invoice</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-5 shadow-sm">
                            <p class="text-sm text-slate-500">Total Semua Nota Toko</p>
                            <p class="mt-2 text-2xl font-bold text-slate-900">Rp {{ number_format($notaTotalAll, 2, ',', '.') }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $notaCountAll }} nota</p>
                        </div>

                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                            <p class="text-sm text-emerald-700">Total Nota Toko Sudah Dibayar</p>
                            <p class="mt-2 text-2xl font-bold text-emerald-800">Rp {{ number_format($notaTotalPaid, 2, ',', '.') }}</p>
                            <p class="mt-1 text-xs text-emerald-700">{{ $notaCountPaid }} nota</p>
                        </div>

                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                            <p class="text-sm text-amber-700">Total Nota Toko Belum Dibayar</p>
                            <p class="mt-2 text-2xl font-bold text-amber-800">Rp {{ number_format($notaTotalUnpaid, 2, ',', '.') }}</p>
                            <p class="mt-1 text-xs text-amber-700">{{ $notaCountUnpaid }} nota</p>
                        </div>
                    </div>
                </div>

                @php
                    $trxSatuan = $dashboardTransactions->filter(fn ($trx) => ($trx['penawaran']->jenis_kontrak ?? 'satuan') === 'satuan');
                    $trxKontrak = $dashboardTransactions->filter(fn ($trx) => ($trx['penawaran']->jenis_kontrak ?? '') === 'kontrak');
                @endphp

                @foreach ([
                    'Detail Item Transaksi (Satuan)' => $trxSatuan,
                    'Detail Item Transaksi (Kontrak)' => $trxKontrak,
                ] as $title => $transactions)
                    <div class="mt-8">
                        <h4 class="font-semibold mb-3">{{ $title }}</h4>
                        @if ($transactions->isEmpty())
                            <p class="text-sm text-gray-500">Belum ada item transaksi.</p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="dashboard-detail-table w-full text-xs sm:text-sm text-left border table-fixed">
                                    <thead class="bg-gray-50">
                                        <tr class="border-b">
                                            <th class="py-2 px-3">Customer</th>
                                            <th class="py-2 px-3">Item</th>
                                            <th class="py-2 px-3">Status Penawaran</th>
                                            <th class="py-2 px-3">Status Purchasing Order</th>
                                            <th class="py-2 px-3">Status Invoice</th>
                                            <th class="py-2 px-3">Status Pembayaran Invoice</th>
                                            <th class="py-2 px-3">Status Pembayaran Pajak</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($transactions as $trx)
                                            @php
                                                $penawaran = $trx['penawaran'];
                                                $invoice = $trx['invoice'];
                                                $fakturPajak = $trx['faktur_pajak'];
                                                $isKontrakSection = $title === 'Detail Item Transaksi (Kontrak)';
                                                $poStatus = '-';
                                                if ($penawaran->status === 'draft') {
                                                    $poStatus = '-';
                                                } elseif ($penawaran->purchasingOrder) {
                                                    $poStatus = $penawaran->purchasingOrder;
                                                } elseif ($penawaran->status === 'approved') {
                                                    $poStatus = 'Upload PO';
                                                }
                                            @endphp
                                            @if ($isKontrakSection && $penawaran->invoices->isNotEmpty())
                                                @foreach ($penawaran->invoices as $invItem)
                                                    <tr class="border-b">
                                                        <td class="py-2 px-3">{{ $penawaran->to_company ?? $penawaran->customer_nama ?? '-' }}</td>
                                                <td class="py-2 px-3">
                                                    @foreach ($penawaran->items as $rowItem)
                                                        <div>{{ $rowItem->nama }}</div>
                                                    @endforeach
                                                </td>
                                                        <td class="py-2 px-3 capitalize">{{ $penawaran->status }}</td>
                                                        <td class="py-2 px-3">
                                                            @if (is_string($poStatus))
                                                                {{ $poStatus }}
                                                            @else
                                                                <a href="{{ asset('storage/' . $poStatus->dokumen_path) }}" target="_blank" class="text-blue-600 hover:text-blue-800">Preview Dokumen</a>
                                                            @endif
                                                        </td>
                                                        <td class="py-2 px-3">
                                                            <a href="{{ route('invoice.show', $invItem) }}" target="_blank" class="text-blue-600 hover:text-blue-800">Preview {{ $invItem->nomor }}</a>
                                                        </td>
                                                        <td class="py-2 px-3">
                                                            @if (($invItem->payment_status ?? 'unpaid') === 'paid')
                                                                <span class="text-emerald-600 font-medium">Sudah Dibayarkan</span>
                                                            @else
                                                                <span class="text-amber-600 font-medium">Belum Dibayarkan</span>
                                                            @endif
                                                        </td>
                                                        <td class="py-2 px-3">
                                                            @php $invFaktur = $invItem->fakturPajak; @endphp
                                                            @if ($invFaktur)
                                                                @if (($invFaktur->payment_status ?? 'unpaid') === 'paid')
                                                                    <span class="text-emerald-600 font-medium">Sudah Dibayarkan</span>
                                                                @else
                                                                    <span class="text-amber-600 font-medium">Belum Dibayarkan</span>
                                                                @endif
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr class="border-b">
                                                    <td class="py-2 px-3">{{ $penawaran->to_company ?? $penawaran->customer_nama ?? '-' }}</td>
                                                    <td class="py-2 px-3">
                                                        @foreach ($penawaran->items as $rowItem)
                                                            <div>{{ $rowItem->nama }}</div>
                                                        @endforeach
                                                    </td>
                                                    <td class="py-2 px-3 capitalize">{{ $penawaran->status }}</td>
                                                    <td class="py-2 px-3">
                                                        @if (is_string($poStatus))
                                                            {{ $poStatus }}
                                                        @else
                                                            <a href="{{ asset('storage/' . $poStatus->dokumen_path) }}" target="_blank" class="text-blue-600 hover:text-blue-800">Preview Dokumen</a>
                                                        @endif
                                                    </td>
                                                    <td class="py-2 px-3">
                                                        @if ($invoice)
                                                            <a href="{{ route('invoice.show', $invoice) }}" class="text-blue-600 hover:text-blue-800">Preview Invoice</a>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td class="py-2 px-3">
                                                        @if ($invoice && ($invoice->payment_status ?? 'unpaid') === 'paid')
                                                            <span class="text-emerald-600 font-medium">Sudah Dibayarkan</span>
                                                        @elseif ($invoice)
                                                            <span class="text-amber-600 font-medium">Belum Dibayarkan</span>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td class="py-2 px-3">
                                                        @if ($fakturPajak)
                                                            @if (($fakturPajak->payment_status ?? 'unpaid') === 'paid')
                                                                <span class="text-emerald-600 font-medium">Sudah Dibayarkan</span>
                                                            @else
                                                                <span class="text-amber-600 font-medium">Belum Dibayarkan</span>
                                                            @endif
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endforeach

            </div>
        </div>
    </div>
</x-app-layout>
