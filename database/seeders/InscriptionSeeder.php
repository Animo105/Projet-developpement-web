<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('inscriptions')->insert([
            ['date' => now(), 'participant_id' => 1, 'tarif_id' => 1, 'bill_id' => 1, 'pay_price' => 250],
            ['date' => now(), 'participant_id' => 1, 'tarif_id' => 3, 'bill_id' => 1, 'pay_price' => 50],
            ['date' => now(), 'participant_id' => 2, 'tarif_id' => 2, 'bill_id' => 1, 'pay_price' => 100],
            ['date' => now(), 'participant_id' => 2, 'tarif_id' => 3, 'bill_id' => 1, 'pay_price' => 50],
            ['date' => now(), 'participant_id' => 2, 'tarif_id' => 4, 'bill_id' => 1, 'pay_price' => -10],
            ['date' => now(), 'participant_id' => 2, 'tarif_id' => 5, 'bill_id' => 1, 'pay_price' => 10],
            ['date' => now(), 'participant_id' => 1, 'tarif_id' => 5, 'bill_id' => 1, 'pay_price' => 10],
            ['date' => now(), 'participant_id' => 1, 'tarif_id' => 7, 'bill_id' => 1, 'pay_price' => 20],
            ['tarif_id' => 2, 'participant_id' => 2, 'bill_id' => 2, 'pay_price' => 25, 'date' => date('Y-m-d')],
            ['tarif_id' => 1, 'participant_id' => 3, 'bill_id' => 2, 'pay_price' => 125, 'date' => date('Y-m-d')],
            ['tarif_id' => 3, 'participant_id' => 3, 'bill_id' => 3, 'pay_price' => 75, 'date' => date('Y-m-d')],
            ['tarif_id' => 4, 'participant_id' => 2, 'bill_id' => 4, 'pay_price' => 220, 'date' => date('Y-m-d')],
            ['tarif_id' => 5, 'participant_id' => 3, 'bill_id' => 5, 'pay_price' => 150, 'date' => date('Y-m-d')],
        ]);
    }
}
