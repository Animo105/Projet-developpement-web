<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PictureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('pictures')->insert([
            ['picture_path' => 'uploads/albums/test1.png', 'created_at' => now(), 'album_id' => 1],
            ['picture_path' => 'uploads/albums/test2.png', 'created_at' => now(), 'album_id' => 2],
            ['picture_path' => 'uploads/albums/test3.png', 'created_at' => now(), 'album_id' => 2],
            ['picture_path' => 'uploads/albums/test4.png', 'created_at' => now(), 'album_id' => 2],
            ['picture_path' => 'uploads/albums/test5.png', 'created_at' => now(), 'album_id' => 2],
            ['picture_path' => 'uploads/albums/test6.png', 'created_at' => now(), 'album_id' => 1],
            ['picture_path' => 'uploads/albums/test7.png', 'created_at' => now(), 'album_id' => 4],
            ['picture_path' => 'uploads/albums/test8.png', 'created_at' => now(), 'album_id' => 4],
            ['picture_path' => 'uploads/albums/test9.png', 'created_at' => now(), 'album_id' => 4],
            ['picture_path' => 'uploads/albums/test10.png', 'created_at' => now(), 'album_id' => 2],
            ['picture_path' => 'uploads/albums/test11.png', 'created_at' => now(), 'album_id' => 2],
            ['picture_path' => 'uploads/albums/test12.png', 'created_at' => now(), 'album_id' => 2],
            ['picture_path' => 'uploads/albums/test13.png', 'created_at' => now(), 'album_id' => 2],
            ['picture_path' => 'uploads/albums/test14.png', 'created_at' => now(), 'album_id' => 2],
            ['picture_path' => 'uploads/albums/test15.png', 'created_at' => now(), 'album_id' => 2],
        ]);
    }
}
