<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penawarans', function (Blueprint $table) {
            $table->string('to_company')->nullable()->after('customer_nama');
            $table->text('to_address')->nullable()->after('to_company');
            $table->decimal('subtotal', 15, 2)->default(0)->after('keterangan');
            $table->decimal('tax_percent', 5, 2)->default(11)->after('subtotal');
            $table->decimal('tax_amount', 15, 2)->default(0)->after('tax_percent');
        });

        Schema::create('penawaran_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penawaran_id')->constrained('penawarans')->cascadeOnDelete();
            $table->string('nama');
            $table->decimal('qty', 12, 2);
            $table->enum('satuan', ['month', 'pcs', 'item']);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penawaran_items');

        Schema::table('penawarans', function (Blueprint $table) {
            $table->dropColumn([
                'to_company',
                'to_address',
                'subtotal',
                'tax_percent',
                'tax_amount',
            ]);
        });
    }
};
