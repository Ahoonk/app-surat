<?php

namespace App\Services;

use App\Models\BeritaAcara;
use App\Models\Company;
use App\Models\DocumentSeries;
use App\Models\Invoice;
use App\Models\SuratJalan;
use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    public function next(Company|int $company, string $documentType, ?string $referenceDate = null): string
    {
        $companyId = $company instanceof Company ? $company->id : $company;

        return DB::transaction(function () use ($companyId, $documentType, $referenceDate) {
            $series = $this->ensureSeries($companyId, $documentType);
            $series = DocumentSeries::query()
                ->where('company_id', $companyId)
                ->where('document_type', $documentType)
                ->lockForUpdate()
                ->firstOrFail();

            $series->counter = max((int) $series->counter, $this->seedCounter($companyId, $documentType));
            $series->counter++;
            $series->save();

            return $this->formatNumber($series, $referenceDate);
        });
    }

    private function ensureSeries(int $companyId, string $documentType): DocumentSeries
    {
        $defaults = $this->defaultsFor($documentType);

        $series = DocumentSeries::firstOrCreate(
            [
                'company_id' => $companyId,
                'document_type' => $documentType,
            ],
            $defaults
        );

        foreach ($defaults as $key => $value) {
            if ($series->{$key} === null || $series->{$key} === '') {
                $series->{$key} = $value;
            }
        }

        if ($series->isDirty()) {
            $series->save();
        }

        return $series;
    }

    private function defaultsFor(string $documentType): array
    {
        return [
            'prefix' => $this->defaultPrefix($documentType),
            'year_mode' => true,
            'month_mode' => true,
            'counter' => 0,
            'padding' => 3,
            'suffix' => $this->defaultSuffix($documentType),
        ];
    }

    private function defaultPrefix(string $documentType): string
    {
        return match ($documentType) {
            'penawaran' => 'PNW',
            'invoice' => 'INV',
            'surat_jalan' => 'SJ',
            'berita_acara' => 'BA',
            'purchasing_order' => 'PO',
            'nota_toko' => 'NT',
            default => strtoupper(str_replace('_', '-', $documentType)),
        };
    }

    private function defaultSuffix(string $documentType): ?string
    {
        return match ($documentType) {
            'invoice' => 'ASK',
            default => null,
        };
    }

    private function seedCounter(int $companyId, string $documentType): int
    {
        return match ($documentType) {
            'invoice' => $this->maxCounterFromCollection(
                Invoice::where('company_id', $companyId)->pluck('nomor')->all()
            ),
            'surat_jalan' => $this->maxCounterFromCollection(
                SuratJalan::where('company_id', $companyId)->pluck('nomor')->all()
            ),
            'berita_acara' => $this->maxCounterFromCollection(
                BeritaAcara::where('company_id', $companyId)->pluck('nomor')->all()
            ),
            default => 0,
        };
    }

    private function maxCounterFromCollection(array $numbers): int
    {
        $max = 0;

        foreach ($numbers as $number) {
            $counter = $this->extractCounter($number);

            if ($counter !== null) {
                $max = max($max, $counter);
            }
        }

        return $max;
    }

    private function extractCounter(?string $number): ?int
    {
        if (empty($number)) {
            return null;
        }

        if (preg_match('/(\d{3,})/', $number, $match)) {
            return (int) $match[1];
        }

        return null;
    }

    private function formatNumber(DocumentSeries $series, ?string $referenceDate = null): string
    {
        return $this->formatParts(
            prefix: $series->prefix ?: $this->defaultPrefix($series->document_type),
            referenceDate: $referenceDate,
            counter: (int) $series->counter,
            padding: (int) ($series->padding ?: 3),
            suffix: $series->suffix ?: $this->defaultSuffix($series->document_type)
        );
    }

    private function formatParts(string $prefix, ?string $referenceDate, int $counter, int $padding, ?string $suffix): string
    {
        $date = $referenceDate ? \Illuminate\Support\Carbon::parse($referenceDate) : now();
        $base = implode('/', [$prefix, $date->format('Y'), $date->format('m'), str_pad((string) $counter, $padding, '0', STR_PAD_LEFT)]);

        return !empty($suffix) ? $base . '-' . $suffix : $base;
    }
}
