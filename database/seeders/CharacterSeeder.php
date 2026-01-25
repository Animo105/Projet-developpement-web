<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CharacterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('characters')->insert([
            ['name'=>'Drac', 'lore'=>"Il étais une fois un dragon appeler Drac", 'participant_id' => 1, 'skill_point' => 0, 'knowledge_point' => 0],
            ['name'=>'Drak', 'lore'=>"Il étais une fois un dragon appeler Drak", 'participant_id' => 2 , 'skill_point' => 5, 'knowledge_point' => 0],
            ['name'=>'Drax', 'lore'=>"Il étais une fois un dragon appeler Drax", 'participant_id' => 3, 'skill_point' => 3, 'knowledge_point' => 2],
            ['name'=>'Alice', 'lore'=>"elle s'est perdu au pays des merveille une fois", 'participant_id' => 4, 'skill_point' => 2, 'knowledge_point' => 1],
        ]);
    }
}
