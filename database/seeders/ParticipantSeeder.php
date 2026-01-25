<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParticipantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('participants')->insert([
            ["password" => '$2y$12$rEFPml80DNzDQygqwpVHBuQsscucFbpqwAB4G677SsXbaoaTcPd7S', "family_name" => "Fistus", "given_name" => "Leonardus", "birthdate" => "2025-09-16", 'user_id'=> 1],
            ["password" => '$2y$12$rEFPml80DNzDQygqwpVHBuQsscucFbpqwAB4G677SsXbaoaTcPd7S', "family_name" => "Jerome", "given_name" => "Bertrand", "birthdate" => "2025-09-16", 'user_id'=> 1],
            ["password" => '$2y$12$rEFPml80DNzDQygqwpVHBuQsscucFbpqwAB4G677SsXbaoaTcPd7S', "family_name" => "Victoria", "given_name" => "Alice", "birthdate" => "2025-09-16", 'user_id'=> 2],
            ["password" => '$2y$12$rEFPml80DNzDQygqwpVHBuQsscucFbpqwAB4G677SsXbaoaTcPd7S', "family_name" => "Adult", "given_name" => "Chui-un", "birthdate" => "2001-09-16", 'user_id'=> 2],
        ]);
    }
}
