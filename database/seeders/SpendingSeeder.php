<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpendingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('spendings')->insert([
            ['name'=>"Épicerie", 'amount'=>300, 'event_id'=>1],
            ['name'=>"costumes", 'amount'=>100, 'event_id'=>1],
        ]);
    }
}
