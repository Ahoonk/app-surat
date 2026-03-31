<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchasing_orders', function (Blueprint $table) {
            $table->string('nomor_po')->nullable()->after('dokumen_name');
            $table->date('tanggal_po')->nullable()->after('nomor_po');
        });
    }

    public function down(): void
    {
        Schema::table('purchasing_orders', function (Blueprint $table) {
            $table->dropColumn(['nomor_po', 'tanggal_po']);
        });
    }
};
