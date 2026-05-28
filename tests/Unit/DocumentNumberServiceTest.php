<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\Mitra;
use App\Models\Penawaran;
use App\Models\User;
use App\Services\DocumentNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentNumberServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_sequential_document_numbers_per_type(): void
    {
        $company = Company::create([
            'name' => 'PT Contoh',
            'address' => 'Jakarta',
            'logo' => null,
        ]);

        $service = app(DocumentNumberService::class);

        $firstInvoice = $service->next($company, 'invoice', '2026-05-21');
        $secondInvoice = $service->next($company->id, 'invoice', '2026-05-21');
        $firstSuratJalan = $service->next($company, 'surat_jalan', '2026-05-21');
        $firstBeritaAcara = $service->next($company, 'berita_acara', '2026-05-21');

        $this->assertSame('INV/2026/05/001-ASK', $firstInvoice);
        $this->assertSame('INV/2026/05/002-ASK', $secondInvoice);
        $this->assertSame('SJ/2026/05/001', $firstSuratJalan);
        $this->assertSame('BA/2026/05/001', $firstBeritaAcara);
    }

    public function test_it_uses_the_last_numeric_segment_when_seeding_existing_invoice_numbers(): void
    {
        $company = Company::create([
            'name' => 'PT Contoh',
            'address' => 'Jakarta',
            'logo' => null,
        ]);

        $user = User::create([
            'name' => 'Tester',
            'email' => 'tester@example.com',
            'password' => 'password',
            'company_id' => $company->id,
            'role' => 'admin',
        ]);

        $penawaran = Penawaran::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'nomor' => 'PNW/2026/05/001',
            'tanggal' => '2026-05-21',
            'customer_nama' => 'Customer',
            'total' => 1000,
            'status' => 'draft',
        ]);

        Invoice::create([
            'company_id' => $company->id,
            'penawaran_id' => $penawaran->id,
            'nomor' => 'INV/2026/05/001-ASK',
            'tanggal' => '2026-05-21',
            'sequence' => 1,
            'total' => 1000,
        ]);

        Invoice::create([
            'company_id' => $company->id,
            'penawaran_id' => $penawaran->id,
            'nomor' => 'INV/2026/05/002-ASK',
            'tanggal' => '2026-05-21',
            'sequence' => 2,
            'total' => 1000,
        ]);

        $service = app(DocumentNumberService::class);

        $nextInvoice = $service->next($company, 'invoice', '2026-05-21');

        $this->assertSame('INV/2026/05/003-ASK', $nextInvoice);
    }

    public function test_it_ignores_corrupted_invoice_numbers_that_do_not_end_with_three_digit_running_numbers(): void
    {
        $company = Company::create([
            'name' => 'PT Contoh',
            'address' => 'Jakarta',
            'logo' => null,
        ]);

        $user = User::create([
            'name' => 'Tester',
            'email' => 'tester@example.com',
            'password' => 'password',
            'company_id' => $company->id,
            'role' => 'admin',
        ]);

        $penawaran = Penawaran::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'nomor' => 'PNW/2026/05/001',
            'tanggal' => '2026-05-21',
            'customer_nama' => 'Customer',
            'total' => 1000,
            'status' => 'draft',
        ]);

        Invoice::create([
            'company_id' => $company->id,
            'penawaran_id' => $penawaran->id,
            'nomor' => 'INV/2026/05/001-ASK',
            'tanggal' => '2026-05-21',
            'sequence' => 1,
            'total' => 1000,
        ]);

        Invoice::create([
            'company_id' => $company->id,
            'penawaran_id' => $penawaran->id,
            'nomor' => 'INV/2026/05/002-ASK',
            'tanggal' => '2026-05-21',
            'sequence' => 2,
            'total' => 1000,
        ]);

        Invoice::create([
            'company_id' => $company->id,
            'penawaran_id' => $penawaran->id,
            'nomor' => 'INV/2026/05/2031-ASK',
            'tanggal' => '2026-05-21',
            'sequence' => 3,
            'total' => 1000,
        ]);

        $service = app(DocumentNumberService::class);

        $nextInvoice = $service->next($company, 'invoice', '2026-05-21');

        $this->assertSame('INV/2026/05/003-ASK', $nextInvoice);
    }

    public function test_aldera_invoice_numbers_continue_across_months_and_ignore_mitra_invoices(): void
    {
        $company = Company::create([
            'name' => 'PT Aldera Saddatech Karya',
            'address' => 'Jakarta',
            'logo' => null,
        ]);

        $user = User::create([
            'name' => 'Tester',
            'email' => 'tester@example.com',
            'password' => 'password',
            'company_id' => $company->id,
            'role' => 'admin',
        ]);

        $alderaPenawaran = Penawaran::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'nomor' => 'PNW/2026/05/001',
            'tanggal' => '2026-05-01',
            'customer_nama' => 'Customer Aldera',
            'total' => 1000,
            'status' => 'draft',
        ]);

        $mitra = Mitra::create([
            'company_id' => $company->id,
            'nama' => 'PT Mitra',
        ]);

        $mitraPenawaran = Penawaran::create([
            'company_id' => $company->id,
            'mitra_id' => $mitra->id,
            'user_id' => $user->id,
            'nomor' => 'PNW/MITRA/001',
            'tanggal' => '2026-05-01',
            'customer_nama' => 'Customer Mitra',
            'total' => 1000,
            'status' => 'draft',
        ]);

        Invoice::create([
            'company_id' => $company->id,
            'penawaran_id' => $alderaPenawaran->id,
            'nomor' => 'INV/2026/01/001-ASK',
            'tanggal' => '2026-01-07',
            'sequence' => 1,
            'total' => 1000,
        ]);

        Invoice::create([
            'company_id' => $company->id,
            'penawaran_id' => $alderaPenawaran->id,
            'nomor' => 'INV/2026/02/002-ASK',
            'tanggal' => '2026-02-11',
            'sequence' => 2,
            'total' => 1000,
        ]);

        Invoice::create([
            'company_id' => $company->id,
            'penawaran_id' => $alderaPenawaran->id,
            'nomor' => 'INV/2026/05/2032-ASK',
            'tanggal' => '2026-05-28',
            'sequence' => 3,
            'total' => 1000,
        ]);

        Invoice::create([
            'company_id' => $company->id,
            'penawaran_id' => $mitraPenawaran->id,
            'nomor' => '999/INV/MTR/V/2026',
            'tanggal' => '2026-05-28',
            'sequence' => 1,
            'total' => 1000,
        ]);

        $service = app(DocumentNumberService::class);

        $nextMayInvoice = $service->nextAlderaInvoice($company, '2026-05-29');

        $this->assertSame('INV/2026/05/003-ASK', $nextMayInvoice);
    }
}
