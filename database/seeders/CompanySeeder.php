<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('companies')->updateOrInsert(
            ['name' => 'PT Aldera Saddatech Karya'],
            [
                'address' => 'Cilegon',
                'logo' => null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
