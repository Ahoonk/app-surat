<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penawarans', function (Blueprint $table) {
            $table->string('invoice_number')->nullable()->after('invoice_date');
            $table->unsignedInteger('invoice_sequence')->default(0)->after('invoice_number');
        });
    }

    public function down(): void
    {
        Schema::table('penawarans', function (Blueprint $table) {
            $table->dropColumn(['invoice_number', 'invoice_sequence']);
        });
    }
};
