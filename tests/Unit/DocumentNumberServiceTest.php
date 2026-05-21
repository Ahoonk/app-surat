<?php

namespace Tests\Unit;

use App\Models\Company;
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
}
