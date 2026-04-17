<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FakturPajakController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PurchasingOrderController;
use App\Http\Controllers\PenawaranController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\SimulasiPembiayaanController;
use App\Http\Controllers\SuratJalanController;
use App\Http\Controllers\NotaTokoController;
use App\Http\Controllers\BeritaAcaraController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\MitraController;
use App\Http\Controllers\TelegramBotController;
use App\Models\FakturPajak;
use App\Models\Invoice;
use App\Models\Penawaran;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', function () {
    $companyId = auth()->user()->company_id;

    $penawaranQuery = Penawaran::where('company_id', $companyId);
    $approvedWithoutPo = (clone $penawaranQuery)
        ->where('status', 'approved')
        ->whereDoesntHave('purchasingOrder')
        ->count();
    $poUploaded = (clone $penawaranQuery)->whereHas('purchasingOrder')->count();

    $invoiceQuery = Invoice::whereHas('penawaran', function ($query) use ($companyId) {
        $query->where('company_id', $companyId);
    });

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

    return view('dashboard', compact('dashboardFinancial', 'dashboardStatus', 'dashboardTax', 'dashboardTransactions'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::post('telegram/webhook', [TelegramBotController::class, 'webhook'])->name('telegram.webhook');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {
    Route::get('penawaran/{penawaran}/pdf', [PenawaranController::class, 'pdf'])->name('penawaran.pdf');
    Route::post('penawaran/{penawaran}/send', [PenawaranController::class, 'send'])->name('penawaran.send');
    Route::get('penawaran-mitra/create', [PenawaranController::class, 'createMitra'])->name('penawaran.mitra.create');
    Route::post('penawaran/{penawaran}/approve-invoice', [PenawaranController::class, 'approveForInvoice'])
        ->name('penawaran.approve-invoice');
    Route::get('purchasing-order', [PurchasingOrderController::class, 'index'])->name('purchasing-order.index');
    Route::post('purchasing-order', [PurchasingOrderController::class, 'store'])->name('purchasing-order.store');
    Route::post('purchasing-order/{penawaran}/create-invoice', [PurchasingOrderController::class, 'createInvoice'])
        ->name('purchasing-order.create-invoice');
    Route::post('purchasing-order/{penawaran}/next-invoice', [PurchasingOrderController::class, 'nextInvoice'])
        ->name('purchasing-order.next-invoice');
    Route::post('purchasing-order/{penawaran}/cancel', [PurchasingOrderController::class, 'cancelApproved'])
        ->name('purchasing-order.cancel');
    Route::get('invoice', [InvoiceController::class, 'index'])->name('invoice.index');
    Route::get('invoice/{invoice}', [InvoiceController::class, 'show'])->name('invoice.show');
    Route::get('invoice/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoice.pdf');
    Route::post('invoice/{invoice}/send', [InvoiceController::class, 'send'])->name('invoice.send');
    Route::post('invoice/{invoice}/update-print-date', [InvoiceController::class, 'updatePrintDate'])->name('invoice.update-print-date');
    Route::post('invoice/{invoice}/verify-payment', [InvoiceController::class, 'verifyPayment'])->name('invoice.verify-payment');
    Route::delete('invoice/{invoice}', [InvoiceController::class, 'destroy'])->name('invoice.destroy');
    Route::get('faktur-pajak', [FakturPajakController::class, 'index'])->name('faktur-pajak.index');
    Route::post('faktur-pajak/{invoice}', [FakturPajakController::class, 'store'])->name('faktur-pajak.store');
    Route::post('faktur-pajak/{invoice}/verify-payment', [FakturPajakController::class, 'verifyPayment'])->name('faktur-pajak.verify-payment');

    Route::resource('penawaran', PenawaranController::class)->only([
        'index',
        'show',
        'create',
        'store',
        'edit',
        'update',
        'destroy',
    ]);

    Route::get('users', [UserManagementController::class, 'index'])->name('users.index');
    Route::post('users', [UserManagementController::class, 'store'])->name('users.store');
    Route::put('users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');

    Route::get('simulasi-pembiayaan', [SimulasiPembiayaanController::class, 'index'])->name('simulasi-pembiayaan.index');
    Route::post('simulasi-pembiayaan', [SimulasiPembiayaanController::class, 'calculate'])->name('simulasi-pembiayaan.calculate');
    Route::get('surat-jalan', [SuratJalanController::class, 'index'])->name('surat-jalan.index');
    Route::get('surat-jalan/{suratJalan}/edit', [SuratJalanController::class, 'edit'])->name('surat-jalan.edit');
    Route::get('surat-jalan/{suratJalan}', [SuratJalanController::class, 'show'])->name('surat-jalan.show');
    Route::put('surat-jalan/{suratJalan}', [SuratJalanController::class, 'update'])->name('surat-jalan.update');
    Route::get('surat-jalan/{suratJalan}/pdf', [SuratJalanController::class, 'pdf'])->name('surat-jalan.pdf');
    Route::post('surat-jalan/{suratJalan}/send', [SuratJalanController::class, 'send'])->name('surat-jalan.send');
    Route::get('berita-acara', [BeritaAcaraController::class, 'index'])->name('berita-acara.index');
    Route::get('berita-acara/{beritaAcara}/edit', [BeritaAcaraController::class, 'edit'])->name('berita-acara.edit');
    Route::get('berita-acara/{beritaAcara}', [BeritaAcaraController::class, 'show'])->name('berita-acara.show');
    Route::put('berita-acara/{beritaAcara}', [BeritaAcaraController::class, 'update'])->name('berita-acara.update');
    Route::get('berita-acara/{beritaAcara}/pdf', [BeritaAcaraController::class, 'pdf'])->name('berita-acara.pdf');
    Route::post('berita-acara/{beritaAcara}/send', [BeritaAcaraController::class, 'send'])->name('berita-acara.send');
    Route::get('nota-toko/{notaToko}/pdf', [NotaTokoController::class, 'pdf'])->name('nota-toko.pdf');
    Route::post('nota-toko/{notaToko}/send', [NotaTokoController::class, 'send'])->name('nota-toko.send');
    Route::post('nota-toko/{notaToko}/verify-payment', [NotaTokoController::class, 'verifyPayment'])->name('nota-toko.verify-payment');
    Route::resource('nota-toko', NotaTokoController::class)->only([
        'index',
        'create',
        'store',
        'show',
        'edit',
        'update',
        'destroy',
    ]);

    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    Route::get('mitra', [MitraController::class, 'index'])->name('mitra.index');
    Route::post('mitra', [MitraController::class, 'store'])->name('mitra.store');
    Route::get('mitra/{mitra}/edit', [MitraController::class, 'edit'])->name('mitra.edit');
    Route::put('mitra/{mitra}', [MitraController::class, 'update'])->name('mitra.update');
    Route::delete('mitra/{mitra}', [MitraController::class, 'destroy'])->name('mitra.destroy');

});
