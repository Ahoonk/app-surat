<?php

use App\Models\Company;
use App\Models\Invoice;
use App\Models\Penawaran;
use App\Services\DocumentNumberService;
use App\Services\DocumentSnapshotService;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Command\Command;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('invoices:renumber-aldera {--company= : Company ID Aldera} {--dry-run}', function () {
    $companyId = $this->option('company')
        ? (int) $this->option('company')
        : Company::where('name', 'PT Aldera Saddatech Karya')->value('id');

    if (!$companyId) {
        $this->error('Company Aldera tidak ditemukan. Isi --company=ID jika nama company berbeda.');

        return Command::FAILURE;
    }

    $invoices = Invoice::query()
        ->with(['penawaran', 'suratJalan', 'beritaAcara'])
        ->where('company_id', $companyId)
        ->whereHas('penawaran', function ($query) {
            $query->whereNull('mitra_id');
        })
        ->orderBy('tanggal')
        ->orderBy('id')
        ->get();

    if ($invoices->isEmpty()) {
        $this->info('Tidak ada invoice Aldera non-mitra yang perlu diproses.');

        return Command::SUCCESS;
    }

    $counter = 1;
    $changes = [];
    $numberService = app(DocumentNumberService::class);

    foreach ($invoices as $invoice) {
        $date = Carbon::parse($invoice->tanggal);
        $counter++;

        $newNumber = sprintf(
            'INV/%s/%s/%03d-ASK',
            $date->format('Y'),
            $date->format('m'),
            $counter
        );

        if ($invoice->nomor === $newNumber) {
            $invoiceNumber = $invoice->nomor;
        } else {
            $invoiceNumber = $newNumber;

            $changes[] = [
                'document' => 'Invoice',
                'model' => $invoice,
                'old_number' => $invoice->nomor,
                'new_number' => $newNumber,
                'tanggal' => $date->toDateString(),
                'customer' => $invoice->penawaran?->to_company ?? $invoice->penawaran?->customer_nama ?? '-',
                'penawaran_id' => $invoice->penawaran_id,
            ];
        }

        $suratJalanNumber = $numberService->alderaNumberFromInvoice($invoiceNumber, 'SJ');

        if ($suratJalanNumber && $invoice->suratJalan && $invoice->suratJalan->nomor !== $suratJalanNumber) {
            $changes[] = [
                'document' => 'Surat Jalan',
                'model' => $invoice->suratJalan,
                'old_number' => $invoice->suratJalan->nomor,
                'new_number' => $suratJalanNumber,
                'tanggal' => Carbon::parse($invoice->suratJalan->tanggal)->toDateString(),
                'customer' => $invoice->penawaran?->to_company ?? $invoice->penawaran?->customer_nama ?? '-',
                'penawaran_id' => $invoice->penawaran_id,
            ];
        }

        $beritaAcaraNumber = $numberService->alderaNumberFromInvoice($invoiceNumber, 'BA');

        if ($beritaAcaraNumber && $invoice->beritaAcara && $invoice->beritaAcara->nomor !== $beritaAcaraNumber) {
            $changes[] = [
                'document' => 'Berita Acara',
                'model' => $invoice->beritaAcara,
                'old_number' => $invoice->beritaAcara->nomor,
                'new_number' => $beritaAcaraNumber,
                'tanggal' => Carbon::parse($invoice->beritaAcara->tanggal)->toDateString(),
                'customer' => $invoice->penawaran?->to_company ?? $invoice->penawaran?->customer_nama ?? '-',
                'penawaran_id' => $invoice->penawaran_id,
            ];
        }
    }

    if (empty($changes)) {
        $this->info('Semua invoice Aldera non-mitra sudah sesuai format.');

        return Command::SUCCESS;
    }

    $this->table(
        ['Dokumen', 'ID', 'Tanggal', 'Customer', 'Nomor Lama', 'Nomor Baru'],
        array_map(fn ($change) => [
            $change['document'],
            $change['model']->id,
            $change['tanggal'],
            $change['customer'],
            $change['old_number'],
            $change['new_number'],
        ], $changes)
    );

    if ($this->option('dry-run')) {
        $this->info(count($changes) . ' nomor dokumen akan berubah. Jalankan tanpa --dry-run untuk menyimpan.');

        return Command::SUCCESS;
    }

    DB::transaction(function () use ($changes) {
        $snapshotService = app(DocumentSnapshotService::class);
        $penawaranIds = [];

        foreach ($changes as $change) {
            $change['model']->update([
                'nomor' => $change['new_number'],
            ]);

            $penawaranIds[$change['penawaran_id']] = true;
        }

        foreach (array_keys($penawaranIds) as $penawaranId) {
            $penawaran = Penawaran::find($penawaranId);

            if (!$penawaran) {
                continue;
            }

            $latestInvoice = $penawaran->invoices()
                ->orderByDesc('sequence')
                ->orderByDesc('id')
                ->first();

            $penawaran->update([
                'invoice_date' => $latestInvoice?->tanggal,
                'invoice_sequence' => $latestInvoice?->sequence,
                'invoice_number' => $latestInvoice?->nomor,
            ]);

            $snapshotService->refreshPenawaranAndRelatedDocuments($penawaran->fresh());
        }
    });

    $this->info(count($changes) . ' nomor dokumen Aldera non-mitra berhasil dinomori ulang.');

    return Command::SUCCESS;
})->purpose('Renumber dokumen Aldera non-mitra agar Surat Jalan dan Berita Acara mengikuti nomor Invoice');
