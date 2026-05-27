<?php

use App\Services\DocumentSnapshotService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penawarans', function (Blueprint $table) {
            $table->json('snapshot_data')->nullable()->after('approved_at');
        });

        $snapshotService = app(DocumentSnapshotService::class);

        \App\Models\Penawaran::query()
            ->with(['company', 'mitra', 'items', 'user'])
            ->chunkById(100, function ($rows) use ($snapshotService): void {
                foreach ($rows as $row) {
                    $row->update([
                        'snapshot_data' => $snapshotService->forPenawaran($row),
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('penawarans', function (Blueprint $table) {
            $table->dropColumn('snapshot_data');
        });
    }
};
