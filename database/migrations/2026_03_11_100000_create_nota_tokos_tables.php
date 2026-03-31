<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nota_tokos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nomor')->unique();
            $table->date('tanggal');
            $table->string('customer_nama');
            $table->string('alamat');
            $table->text('keterangan')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(11);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('nota_toko_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nota_toko_id')->constrained()->cascadeOnDelete();
            $table->string('nama');
            $table->decimal('qty', 12, 2)->default(1);
            $table->enum('satuan', ['month', 'pcs', 'item', 'unit'])->default('pcs');
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nota_toko_items');
        Schema::dropIfExists('nota_tokos');
    }
};
