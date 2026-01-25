<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CharacterCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('character_cards')->insert([
            ["card_id" => 1, "character_id" => 1, "is_owned" => true],
            ["card_id" => 2, "character_id" => 2, "is_owned" => true],
            ["card_id" => 3, "character_id" => 2, "is_owned" => true],
            ["card_id" => 4, "character_id" => 2, "is_owned" => true],
            ["card_id" => 56, "character_id" => 2, "is_owned" => true],
            ["card_id" => 5, "character_id" => 3, "is_owned" => true],
            ["card_id" => 6, "character_id" => 3, "is_owned" => true],
            ["card_id" => 7, "character_id" => 3, "is_owned" => true],
            ["card_id" => 8, "character_id" => 3, "is_owned" => true],
            ["card_id" => 9, "character_id" => 3, "is_owned" => true],
        ]);
    }
}
