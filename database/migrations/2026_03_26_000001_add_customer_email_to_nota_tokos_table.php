<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nota_tokos', function (Blueprint $table) {
            $table->string('customer_email')->nullable()->after('customer_nama');
        });
    }

    public function down(): void
    {
        Schema::table('nota_tokos', function (Blueprint $table) {
            $table->dropColumn('customer_email');
        });
    }
};
