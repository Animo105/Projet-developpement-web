<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use function Illuminate\Events\queueable;

class AlbumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('albums')->insert([
            ['name' => 'test 1', 'year' => 2024],
            ['name' => 'test 2', 'year' => 2025],
            ['name' => 'test 3', 'year' => 2025],
            ['name' => 'test 4', 'year' => 2026],
        ]);
    }
}
