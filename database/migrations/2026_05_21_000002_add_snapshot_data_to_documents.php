<?php

use App\Models\BeritaAcara;
use App\Models\Invoice;
use App\Models\NotaToko;
use App\Models\SuratJalan;
use App\Services\DocumentSnapshotService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->json('snapshot_data')->nullable()->after('created_by');
        });

        Schema::table('surat_jalans', function (Blueprint $table) {
            $table->json('snapshot_data')->nullable()->after('created_by');
        });

        Schema::table('berita_acaras', function (Blueprint $table) {
            $table->json('snapshot_data')->nullable()->after('created_by');
        });

        Schema::table('nota_tokos', function (Blueprint $table) {
            $table->json('snapshot_data')->nullable()->after('payment_date');
        });

        $snapshotService = app(DocumentSnapshotService::class);

        Invoice::query()->chunkById(100, function ($rows) use ($snapshotService) {
            foreach ($rows as $row) {
                $row->update([
                    'snapshot_data' => $snapshotService->forInvoice($row),
                ]);
            }
        });

        SuratJalan::query()->chunkById(100, function ($rows) use ($snapshotService) {
            foreach ($rows as $row) {
                $row->update([
                    'snapshot_data' => $snapshotService->forSuratJalan($row),
                ]);
            }
        });

        BeritaAcara::query()->chunkById(100, function ($rows) use ($snapshotService) {
            foreach ($rows as $row) {
                $row->update([
                    'snapshot_data' => $snapshotService->forBeritaAcara($row),
                ]);
            }
        });

        NotaToko::query()->chunkById(100, function ($rows) use ($snapshotService) {
            foreach ($rows as $row) {
                $row->update([
                    'snapshot_data' => $snapshotService->forNotaToko($row),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('nota_tokos', function (Blueprint $table) {
            $table->dropColumn('snapshot_data');
        });

        Schema::table('berita_acaras', function (Blueprint $table) {
            $table->dropColumn('snapshot_data');
        });

        Schema::table('surat_jalans', function (Blueprint $table) {
            $table->dropColumn('snapshot_data');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('snapshot_data');
        });
    }
};
