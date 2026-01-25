<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use function Laravel\Prompts\table;

class CardTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('card_types')->insert([
            ["name" => "Technique"],
            ["name" => "Compétence"],
            ["name" => "Recette Potion"],
            ["name" => "Rituel"],
            ["name" => "Sortilège"],
            ["name" => "Entrainement"],
            ["name" => "Recette Thé"],
        ]);
    }
}
