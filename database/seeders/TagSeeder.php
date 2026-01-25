<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tags')->insert([
            ["name" => "Perk", "description" => ""],
            ["name" => "Ressource", "description" => ""],
            ["name" => "Mana", "description" => ""],
            ["name" => "Métier", "description" => ""],
            ["name" => "Expertise", "description" => ""],
            ["name" => "Statistique", "description" => ""],
            ["name" => "Inventaire", "description" => ""],

        ]);
    }
}
