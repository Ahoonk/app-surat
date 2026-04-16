<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penawarans', function (Blueprint $table) {
            $table->foreignId('mitra_id')->nullable()->after('company_id')->constrained('mitras')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('penawarans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('mitra_id');
        });
    }
};
