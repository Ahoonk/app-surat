<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\DocumentSeries;
use Illuminate\Database\Seeder;

class DocumentSeriesSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'penawaran' => [
                'prefix' => 'PNW',
                'suffix' => null,
            ],
            'invoice' => [
                'prefix' => 'INV',
                'suffix' => 'ASK',
            ],
            'surat_jalan' => [
                'prefix' => 'SJ',
                'suffix' => null,
            ],
            'berita_acara' => [
                'prefix' => 'BA',
                'suffix' => null,
            ],
            'purchasing_order' => [
                'prefix' => 'PO',
                'suffix' => null,
            ],
            'nota_toko' => [
                'prefix' => 'NT',
                'suffix' => null,
            ],
        ];

        Company::query()->chunkById(100, function ($companies) use ($defaults) {
            foreach ($companies as $company) {
                foreach ($defaults as $documentType => $config) {
                    $series = DocumentSeries::firstOrCreate(
                        [
                            'company_id' => $company->id,
                            'document_type' => $documentType,
                        ],
                        [
                            'prefix' => $config['prefix'],
                            'year_mode' => true,
                            'month_mode' => true,
                            'counter' => 0,
                            'padding' => 3,
                            'suffix' => $config['suffix'],
                        ]
                    );

                    $dirty = false;

                    foreach ([
                        'prefix' => $config['prefix'],
                        'year_mode' => true,
                        'month_mode' => true,
                        'padding' => 3,
                        'suffix' => $config['suffix'],
                    ] as $key => $value) {
                        if ($series->{$key} === null || $series->{$key} === '') {
                            $series->{$key} = $value;
                            $dirty = true;
                        }
                    }

                    if ($dirty) {
                        $series->save();
                    }
                }
            }
        });
    }
}
