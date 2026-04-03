<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mitras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            $table->string('nama');
            $table->string('email')->nullable();
            $table->string('alamat')->nullable();

            $table->string('nomor_penawaran')->nullable();
            $table->string('nomor_invoice')->nullable();
            $table->string('nomor_surat_jalan')->nullable();
            $table->string('nomor_berita_acara')->nullable();

            $table->string('template_penawaran_path')->nullable();
            $table->string('template_invoice_path')->nullable();
            $table->string('template_surat_jalan_path')->nullable();
            $table->string('template_berita_acara_path')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mitras');
    }
};
