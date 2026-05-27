<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\DocumentTemplate;
use Illuminate\Database\Seeder;

class DocumentTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'penawaran' => [
                'name' => 'Default Surat Penawaran',
                'file_path' => 'penawaran.pdf',
            ],
            'invoice' => [
                'name' => 'Default Invoice',
                'file_path' => 'invoice.pdf',
            ],
            'surat_jalan' => [
                'name' => 'Default Surat Jalan',
                'file_path' => 'surat-jalan.pdf',
            ],
            'berita_acara' => [
                'name' => 'Default Berita Acara',
                'file_path' => 'berita-acara.pdf',
            ],
            'nota_toko' => [
                'name' => 'Default Nota Toko',
                'file_path' => 'nota-toko.pdf',
            ],
        ];

        Company::query()->chunkById(100, function ($companies) use ($defaults) {
            foreach ($companies as $company) {
                foreach ($defaults as $documentType => $template) {
                    DocumentTemplate::firstOrCreate(
                        [
                            'company_id' => $company->id,
                            'document_type' => $documentType,
                            'is_default' => true,
                        ],
                        [
                            'name' => $template['name'],
                            'file_path' => $template['file_path'],
                        ]
                    );
                }
            }
        });
    }
}
