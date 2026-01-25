<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('events')->insert([
            ['name' => 'Evenement d\'été 2025', 'description' => 'De 9h à 19h le samedi et de 9h à 3h le dimanche.', 'start_date' => '2025-03-022', 'end_date' => '2025-03-24', 'end_date_registration' => '2025-03-15'],
            ['name' => 'Printemps 2025', 'description'=> 'Patate', 'start_date' => '2025-11-02', 'end_date' => '2025-11-04', 'end_date_registration' => '2025-11-02'],
            ['name' => 'Été 2025', 'description'=> 'Patate', 'start_date' => '2025-11-02', 'end_date' => '2025-11-04', 'end_date_registration' => '2025-11-02'],
            ['name' => 'Automne 2025', 'description'=> 'Patate', 'start_date' => '2025-11-02', 'end_date' => '2025-11-04', 'end_date_registration' => '2025-11-02']
        ]);
    }
}
