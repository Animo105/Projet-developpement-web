<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use function Laravel\Prompts\note;

class BillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('bills')->insert([
            ['due_date' => date('Y-m-d'), 'status_id' => 1, 'user_id' => 2, 'note' => "Test 1-2-3", "event_id" => 1],
            ['due_date' => date('Y-m-d'), 'status_id' => 1, 'user_id' => 2, 'note' => "Test 2", "event_id" => 1],
            ['due_date' => date('Y-m-d'), 'status_id' => 1, 'user_id' => 3, 'note' => "Test 3", "event_id" => 1],
            ['due_date' => date('Y-m-d'), 'status_id' => 1, 'user_id' => 2, 'note' => "Test 4", "event_id" => 1],
            ['due_date' => date('Y-m-d'), 'status_id' => 1, 'user_id' => 3, 'note' => "Test 5", "event_id" => 1],
        ]);
    }
}
