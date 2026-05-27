<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\DocumentTemplate;
use App\Services\DocumentTemplateResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentTemplateResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_uses_company_document_template_when_available(): void
    {
        $company = Company::create([
            'name' => 'PT Contoh',
            'address' => 'Jakarta',
            'logo' => null,
        ]);

        DocumentTemplate::create([
            'company_id' => $company->id,
            'document_type' => 'invoice',
            'name' => 'Invoice Default',
            'file_path' => 'invoice.pdf',
            'is_default' => true,
        ]);

        $resolver = app(DocumentTemplateResolver::class);

        $this->assertSame('invoice.pdf', $resolver->resolveView($company->id, 'invoice', 'fallback.pdf'));
        $this->assertSame('fallback.pdf', $resolver->resolveView($company->id, 'surat_jalan', 'fallback.pdf'));
    }
}
