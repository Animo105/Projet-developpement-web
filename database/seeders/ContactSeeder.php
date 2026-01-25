<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('contacts')->insert([
            ["family_name" => "contact", "given_name" => "supplémentaire", "email" => "sup@sup.com", "phone" => "1212122121", "user_id" => 2],
            ["family_name" => "contact2", "given_name" => "supplémentaire2", "email" => "", "phone" => "2323323232", "user_id" => 2],
            ["family_name" => "contact3", "given_name" => "supplémentaire3", "email" => "sup2@sup.com", "phone" => "2323223232", "user_id" => 2],
            ["family_name" => "admin", "given_name" => "daddy", "email" => "", "phone" => "2323323232", "user_id" => 1]
        ]);
    }
}
