<?php

namespace Database\Seeders;

use App\Models\Icon;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(5)->create()->each(function ($user) {
            Participant::factory(rand(2, 3))->for($user)->create();
        });


        $this->call(
            [
                UserSeeder::class,
                TagSeeder::class,
                ContactSeeder::class,
                ParticipantSeeder::class,
                CharacterSeeder::class,
                AccessorySeeder::class,

                EventSeeder::class,
                TarifSeeder::class,
                StatusSeeder::class,
                BillSeeder::class,
                MethodSeeder::class,
                InscriptionSeeder::class,

                IconSeeder::class,
                ElementSeeder::class,
                TagElementSeeder::class,
                ElementElementSeeder::class,
                CardElementTypeSeeder::class,
                CardTypeSeeder::class,
                CardSeeder::class,
                CardElementSeeder::class,
                NodeSeeder::class,
                CharacterCardSeeder::class,

                AlbumSeeder::class,
                PictureSeeder::class,
            ]
        );

    }
}
