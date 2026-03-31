<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE penawaran_items MODIFY satuan ENUM('month','pcs','item','unit') NOT NULL");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("UPDATE penawaran_items SET satuan = 'item' WHERE satuan = 'unit'");
            DB::statement("ALTER TABLE penawaran_items MODIFY satuan ENUM('month','pcs','item') NOT NULL");
        }
    }
};
