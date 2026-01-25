<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ElementElementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('element_element')->insert([
            ["element_id" => 13, "another_element_id" => 1],
            ["element_id" => 13, "another_element_id" => 9],
            ["element_id" => 14, "another_element_id" => 5],
            ["element_id" => 14, "another_element_id" => 9],
            ["element_id" => 15, "another_element_id" => 2],
            ["element_id" => 15, "another_element_id" => 9],
            ["element_id" => 16, "another_element_id" => 3],
            ["element_id" => 16, "another_element_id" => 9],
            ["element_id" => 17, "another_element_id" => 4],
            ["element_id" => 17, "another_element_id" => 9],
            ["element_id" => 18, "another_element_id" => 1],
            ["element_id" => 18, "another_element_id" => 11],
            ["element_id" => 19, "another_element_id" => 5],
            ["element_id" => 19, "another_element_id" => 11],
            ["element_id" => 20, "another_element_id" => 2],
            ["element_id" => 20, "another_element_id" => 11],
            ["element_id" => 21, "another_element_id" => 3],
            ["element_id" => 21, "another_element_id" => 11],
            ["element_id" => 22, "another_element_id" => 4],
            ["element_id" => 22, "another_element_id" => 11],
            ["element_id" => 23, "another_element_id" => 1],
            ["element_id" => 23, "another_element_id" => 8],
            ["element_id" => 24, "another_element_id" => 5],
            ["element_id" => 24, "another_element_id" => 8],
            ["element_id" => 25, "another_element_id" => 2],
            ["element_id" => 25, "another_element_id" => 8],
            ["element_id" => 26, "another_element_id" => 3],
            ["element_id" => 26, "another_element_id" => 8],
            ["element_id" => 27, "another_element_id" => 4],
            ["element_id" => 27, "another_element_id" => 8],
            ["element_id" => 28, "another_element_id" => 1],
            ["element_id" => 28, "another_element_id" => 10],
            ["element_id" => 29, "another_element_id" => 2],
            ["element_id" => 29, "another_element_id" => 10],
            ["element_id" => 30, "another_element_id" => 3],
            ["element_id" => 30, "another_element_id" => 10],
            ["element_id" => 31, "another_element_id" => 4],
            ["element_id" => 31, "another_element_id" => 10],
            ["element_id" => 32, "another_element_id" => 1],
            ["element_id" => 32, "another_element_id" => 12],
            ["element_id" => 33, "another_element_id" => 5],
            ["element_id" => 33, "another_element_id" => 12],
            ["element_id" => 34, "another_element_id" => 2],
            ["element_id" => 34, "another_element_id" => 12],
            ["element_id" => 35, "another_element_id" => 3],
            ["element_id" => 35, "another_element_id" => 12],
            ["element_id" => 36, "another_element_id" => 4],
            ["element_id" => 36, "another_element_id" => 12],
            ["element_id" => 50, "another_element_id" => 5],
            ["element_id" => 50, "another_element_id" => 10],
        ]);
    }
}
