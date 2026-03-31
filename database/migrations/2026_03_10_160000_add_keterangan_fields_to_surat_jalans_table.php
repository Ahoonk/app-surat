<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_jalans', function (Blueprint $table) {
            $table->string('pemberi_nama')->nullable()->after('tanggal');
            $table->string('pemberi_jabatan')->nullable()->after('pemberi_nama');
            $table->text('pemberi_alamat')->nullable()->after('pemberi_jabatan');
            $table->string('penerima_nama')->nullable()->after('pemberi_alamat');
            $table->string('penerima_hp')->nullable()->after('penerima_nama');
        });
    }

    public function down(): void
    {
        Schema::table('surat_jalans', function (Blueprint $table) {
            $table->dropColumn(['pemberi_nama', 'pemberi_jabatan', 'pemberi_alamat', 'penerima_nama', 'penerima_hp']);
        });
    }
};
