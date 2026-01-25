<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('statuses')->insert([
            ['name' => "Accepté"],
            ['name' => "En attente de paiement"],
            ['name' => "En cours de vérification"],
            ['name' => "En attente de remboursement"],
            ['name' => "Remboursé"]
        ]);
    }
}
