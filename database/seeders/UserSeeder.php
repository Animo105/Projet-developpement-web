<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //password =user123
        DB::table('users')->insert([
            ["family_name" => "admin", "given_name" => "admin", "email" => "admin@admin.com", "phone" => "(200) 649-0009", "password" => '$2y$12$rEFPml80DNzDQygqwpVHBuQsscucFbpqwAB4G677SsXbaoaTcPd7S', 'role'=>UserRole::Admin],
            ["family_name" => "user", "given_name" => "user", "email" => "user@user.com", "phone" => "(200) 643-0137", "password" => '$2y$12$rEFPml80DNzDQygqwpVHBuQsscucFbpqwAB4G677SsXbaoaTcPd7S', 'role'=>UserRole::User],
            ["family_name" => "user2", "given_name" => "user2", "email" => "user2@user.com", "phone" => "(700) 224-8974", "password" => '$2y$12$rEFPml80DNzDQygqwpVHBuQsscucFbpqwAB4G677SsXbaoaTcPd7S', 'role'=>UserRole::User],
        ]);
    }
}
