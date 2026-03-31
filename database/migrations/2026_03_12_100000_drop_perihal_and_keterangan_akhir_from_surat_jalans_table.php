<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_jalans', function (Blueprint $table) {
            if (Schema::hasColumn('surat_jalans', 'perihal')) {
                $table->dropColumn('perihal');
            }
            if (Schema::hasColumn('surat_jalans', 'keterangan_akhir')) {
                $table->dropColumn('keterangan_akhir');
            }
        });
    }

    public function down(): void
    {
        Schema::table('surat_jalans', function (Blueprint $table) {
            if (!Schema::hasColumn('surat_jalans', 'perihal')) {
                $table->string('perihal')->nullable()->after('kota_tanggal_manual');
            }
            if (!Schema::hasColumn('surat_jalans', 'keterangan_akhir')) {
                $table->text('keterangan_akhir')->nullable()->after('perihal');
            }
        });
    }
};
