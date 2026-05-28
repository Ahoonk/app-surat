<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\Invoice;
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
}
