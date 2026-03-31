<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchasing_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penawaran_id')->unique()->constrained('penawarans')->cascadeOnDelete();
            $table->string('dokumen_path');
            $table->string('dokumen_name');
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('uploaded_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchasing_orders');
    }
};
