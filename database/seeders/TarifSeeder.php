<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TarifSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tarifs')->insert([
            ['event_id' => 1, 'type' => 'participant', 'price' => 250],
            ['event_id' => 1, 'type' => 'accompagnateur', 'price' => 100],
            ['event_id' => 1, 'type' => 'arme', 'price' => 50],
            ['event_id' => 1, 'type' => 'costume', 'price' => 10],
            ['event_id' => 1, 'type' => 'depot', 'price' => 10],
            ['event_id' => 1, 'type' => 'rfratrie', 'price' => 20],
            ['event_id' => 2, 'type' => 'participant', 'price' => 75],
            ['event_id' => 2, 'type' => 'accompagnateur', 'price' => 100],
            ['event_id' => 2, 'type' => 'arme', 'price' => 20],
            ['event_id' => 2, 'type' => 'costume', 'price' => 30],
            ['event_id' => 3, 'type' => 'participant', 'price' => 150],
            ['event_id' => 3, 'type' => 'accompagnateur', 'price' => 100],
            ['event_id' => 3, 'type' => 'costume', 'price' => 30],
            ['event_id' => 4, 'type' => 'participant', 'price' => 200],
            ['event_id' => 4, 'type' => 'accompagnateur', 'price' => 100],
            ['event_id' => 4, 'type' => 'arme', 'price' => 10],

        ]);
    }
}
