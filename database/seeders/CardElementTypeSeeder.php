<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CardElementTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('card_element_types')->insert([
            ["name" => "Coût", "description" => "Coût d'une carte"],
            ["name" => "Perk", "description" => "Effet additionel d'une carte"],
            ["name" => "Essence", "description" => ""],
        ]);
    }
}
