<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('cards')->insert(values: [
            /* 1 */["title" => "Concoction mixte", "stock" => 1, "level" => 0, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C01.JPG"],
            /* 2 */["title" => "Concoction", "stock" => 1, "level" => 0, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C02.JPG"],
            /* 3 */["title" => "Feu alchimique", "stock" => 1, "level" => 0, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C03.JPG"],
            /* 4 */["title" => "Potion paralysante", "stock" => 1, "level" => 0, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C04.JPG"],
            /* 5 */["title" => "Premier soin", "stock" => 1, "level" => 0, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C05.JPG"],
            /* 6 */["title" => "Remontant", "stock" => 1, "level" => 0, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C06.JPG"],
            /* 7 */["title" => "Vivifiant", "stock" => 1, "level" => 0, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C07.JPG"],
            /* 8 */["title" => "Concocteur efficace", "stock" => 1, "level" => 1, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C08.JPG"],
            /* 9 */["title" => "Distillation arcanique", "stock" => 1, "level" => 1, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C09.JPG"],
            /* 10 */["title" => "Poussiere de mana", "stock" => 1, "level" => 1, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C10.JPG"],
            /* 11 */["title" => "Soins experts", "stock" => 1, "level" => 1, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C11.JPG"],
            /* 12 */["title" => "Soins rapide", "stock" => 1, "level" => 1, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C12.JPG"],
            /* 13 */["title" => "The de vigilance", "stock" => 1, "level" => 1, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C13.JPG"],
            /* 14 */["title" => "Medecine de guerre", "stock" => 1, "level" => 1, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C14.JPG"],
            /* 15 */["title" => "Invention de recette", "stock" => 1, "level" => 2, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C15.JPG"],
            /* 16 */["title" => "Additif energetique", "stock" => 1, "level" => 2, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C16.JPG"],
            /* 17 */["title" => "Potion du grand repos", "stock" => 1, "level" => 2, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C17.JPG"],
            /* 18 */["title" => "The de l'evasion", "stock" => 1, "level" => 2, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C18.JPG"],
            /* 19 */["title" => "The de resistance", "stock" => 1, "level" => 2, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C19.JPG"],
            /* 20 */["title" => "Transmutation alchimique", "stock" => 1, "level" => 2, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C20.JPG"],
            /* 21 */["title" => "Soigneur de brousse", "stock" => 1, "level" => 2, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C21.JPG"],
            /* 22 */["title" => "Dillution arcanique", "stock" => 1, "level" => 3, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C22.JPG"],
            /* 23 */["title" => "Essence de l'ombre", "stock" => 1, "level" => 3, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C23.JPG"],
            /* 24 */["title" => "Infusion de mana sauvage", "stock" => 1, "level" => 3, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C24.JPG"],
            /* 25 */["title" => "Le sang des Sylvetre", "stock" => 1, "level" => 3, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C25.JPG"],
            /* 26 */["title" => "Maitre guerisseur", "stock" => 1, "level" => 3, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C26.JPG"],
            /* 27 */["title" => "Potion de furie guerriere", "stock" => 1, "level" => 3, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C27.JPG"],
            /* 28 */["title" => "Instinc du soigneur", "stock" => 1, "level" => 3, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C28.JPG"],

            /* 29 */["title" => "Chef de cérémonie", "stock" => 1, "level" => 0, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C29.JPG"],
            /* 30 */["title" => "Contrôle du mana", "stock" => 1, "level" => 0, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C30.JPG"],
            /* 31 */["title" => "Force de la nature", "stock" => 1, "level" => 0, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C31.JPG"],
            /* 32 */["title" => "Invocateur", "stock" => 1, "level" => 0, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C32.JPG"],
            /* 33 */["title" => "Magie de guerre", "stock" => 1, "level" => 0, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C33.JPG"],
            /* 34 */["title" => "Souvenirs ancestraux", "stock" => 1, "level" => 0, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C34.JPG"],
            /* 35 */["title" => "Mage de combat", "stock" => 1, "level" => 1, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C35.JPG"],
            /* 36 */["title" => "Le don du feu", "stock" => 1, "level" => 1, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C36.JPG"],
            /* 37 */["title" => "Partage de vitalité", "stock" => 1, "level" => 1, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C37.JPG"],
            /* 38 */["title" => "Sagesse des ancêtres", "stock" => 1, "level" => 1, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C38.JPG"],
            /* 39 */["title" => "Sanctuaire druidique", "stock" => 1, "level" => 1, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C39.JPG"],
            /* 40 */["title" => "Soif de connaissance", "stock" => 1, "level" => 1, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C40.JPG"],
            /* 41 */["title" => "Grimoire Magique", "stock" => 1, "level" => 1, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C41.JPG"],
            /* 42 */["title" => "Maitrise du pouvoir", "stock" => 1, "level" => 2, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C42.JPG"],
            /* 43 */["title" => "Bénédiction de la nature", "stock" => 1, "level" => 2, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C43.JPG"],
            /* 44 */["title" => "Coeur de braise", "stock" => 1, "level" => 2, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C44.JPG"],
            /* 45 */["title" => "Le Silence de la nuit", "stock" => 1, "level" => 2, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C45.JPG"],
            /* 46 */["title" => "Perception de la vérité", "stock" => 1, "level" => 2, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C46.JPG"],
            /* 47 */["title" => "Regard des arcanes", "stock" => 1, "level" => 2, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C47.JPG"],
            /* 48 */["title" => "Grimoire du maître", "stock" => 1, "level" => 2, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C48.JPG"],
            /* 49 */["title" => "Grand mage de guerre", "stock" => 1, "level" => 3, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C49.JPG"],
            /* 50 */["title" => "Esprit enflammé", "stock" => 1, "level" => 3, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C50.JPG"],
            /* 51 */["title" => "Fontaine de jouvence", "stock" => 1, "level" => 3, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C51.JPG"],
            /* 52 */["title" => "Immunité diplomatique", "stock" => 1, "level" => 3, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C52.JPG"],
            /* 53 */["title" => "Puissance stellaire", "stock" => 1, "level" => 3, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C53.JPG"],
            /* 54 */["title" => "Symbiose avec la nature", "stock" => 1, "level" => 3, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C54.JPG"],
            /* 55 */["title" => "Archimage", "stock" => 1, "level" => 3, "version" => 1, "type_id" => 2, "description" => "", "image_path" => "uploads/C55.JPG"],
            // *56 Statistic test */
            ["title" => "Guerrier Vétéran", "stock" => 1, "level" => 1, "version" => 1, "type_id" => 6, "description" => "", "image_path" => ""],
        ]);
    }
}
