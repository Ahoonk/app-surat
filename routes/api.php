<?php

use App\Http\Controllers\Api\CustomerController as ApiCustomerController;
use App\Http\Controllers\Api\InvoiceController as ApiInvoiceController;
use App\Http\Controllers\Api\MitraController as ApiMitraController;
use App\Http\Controllers\Api\PenawaranController as ApiPenawaranController;
use App\Http\Controllers\Api\BootstrapController;
use App\Http\Controllers\Api\BeritaAcaraController as ApiBeritaAcaraController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SuratJalanController as ApiSuratJalanController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('bootstrap', [BootstrapController::class, 'show'])->name('api.bootstrap');
    Route::get('customers', [ApiCustomerController::class, 'index'])->name('api.customers.index');
    Route::get('mitras', [ApiMitraController::class, 'index'])->name('api.mitras.index');
    Route::get('penawarans/meta', [ApiPenawaranController::class, 'meta'])->name('api.penawarans.meta');
    Route::get('penawarans', [ApiPenawaranController::class, 'index'])->name('api.penawarans.index');
    Route::post('penawarans', [ApiPenawaranController::class, 'store'])->name('api.penawarans.store');
    Route::get('penawarans/{penawaran}', [ApiPenawaranController::class, 'show'])->name('api.penawarans.show');
    Route::post('penawarans/{penawaran}/send', [ApiPenawaranController::class, 'send'])->name('api.penawarans.send');
    Route::put('penawarans/{penawaran}', [ApiPenawaranController::class, 'update'])->name('api.penawarans.update');
    Route::delete('penawarans/{penawaran}', [ApiPenawaranController::class, 'destroy'])->name('api.penawarans.destroy');
    Route::get('invoices', [ApiInvoiceController::class, 'index'])->name('api.invoices.index');
    Route::get('invoices/{invoice}', [ApiInvoiceController::class, 'show'])->name('api.invoices.show');
    Route::post('invoices/{invoice}/send', [ApiInvoiceController::class, 'send'])->name('api.invoices.send');
    Route::post('invoices/{invoice}/update-print-date', [ApiInvoiceController::class, 'updatePrintDate'])->name('api.invoices.update-print-date');
    Route::post('invoices/{invoice}/verify-payment', [ApiInvoiceController::class, 'verifyPayment'])->name('api.invoices.verify-payment');
    Route::get('surat-jalans', [ApiSuratJalanController::class, 'index'])->name('api.surat-jalans.index');
    Route::get('surat-jalans/{suratJalan}', [ApiSuratJalanController::class, 'show'])->name('api.surat-jalans.show');
    Route::post('surat-jalans/{suratJalan}/send', [ApiSuratJalanController::class, 'send'])->name('api.surat-jalans.send');
    Route::get('berita-acaras', [ApiBeritaAcaraController::class, 'index'])->name('api.berita-acaras.index');
    Route::get('berita-acaras/{beritaAcara}', [ApiBeritaAcaraController::class, 'show'])->name('api.berita-acaras.show');
    Route::post('berita-acaras/{beritaAcara}/send', [ApiBeritaAcaraController::class, 'send'])->name('api.berita-acaras.send');
});
