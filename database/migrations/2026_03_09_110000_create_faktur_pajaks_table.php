<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faktur_pajaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->unique()->constrained('invoices')->cascadeOnDelete();
            $table->string('dokumen_path');
            $table->string('dokumen_name');
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('uploaded_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faktur_pajaks');
    }
};
