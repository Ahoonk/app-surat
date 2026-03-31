<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penawaran_id')->constrained('penawarans')->cascadeOnDelete();
            $table->foreignId('purchasing_order_id')->nullable()->constrained('purchasing_orders')->nullOnDelete();
            $table->string('nomor');
            $table->date('tanggal');
            $table->unsignedInteger('sequence')->default(1);
            $table->decimal('total', 15, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
