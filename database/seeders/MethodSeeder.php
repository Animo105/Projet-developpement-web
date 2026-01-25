<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MethodSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('methods')->insert([
            ['name' => 'Chèque'],
            ['name' => 'Virement'],
            ['name' => 'Liquide'],
        ]);
    }
}
