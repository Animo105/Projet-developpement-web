<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ElementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('elements')->insert(values: [
            ["name" => "Mana Bleu", "icon_id" => 1],
            ["name" => "Mana Vert", "icon_id" => 2],
            ["name" => "Mana Rouge", "icon_id" => 3],
            ["name" => "Mana Gris", "icon_id" => 4],
            ["name" => "Mana Blanc", "icon_id" => 5],
            ["name" => "Mana Générique", "icon_id" => 6],
            ["name" => "Mana Partagé", "icon_id" => 7],
            ["name" => "Fabricant", "icon_id" => 8],
            ["name" => "Combattant", "icon_id" => 9],
            ["name" => "Érudit", "icon_id" => 10],
            ["name" => "Concocteur", "icon_id" => 11],
            ["name" => "Récolteur", "icon_id" => 12],
            ["name" => "Tacticien", "icon_id" => 13],
            ["name" => "Chevalier", "icon_id" => 14],
            ["name" => "Rodeur", "icon_id" => 15],
            ["name" => "Barbare", "icon_id" => 16],
            ["name" => "Moine", "icon_id" => 17],
            ["name" => "Savant", "icon_id" => 18],
            ["name" => "Médecin", "icon_id" => 19],
            ["name" => "Herbaliste", "icon_id" => 20],
            ["name" => "Alchemiste", "icon_id" => 21],
            ["name" => "Infuseur", "icon_id" => 22],
            ["name" => "Enchanteur", "icon_id" => 23],
            ["name" => "Aubergiste", "icon_id" => 24],
            ["name" => "Charpentier", "icon_id" => 25],
            ["name" => "Forgeron", "icon_id" => 26],
            ["name" => "Joailler", "icon_id" => 27],
            ["name" => "Magicien", "icon_id" => 28],
            ["name" => "Druide", "icon_id" => 29],
            ["name" => "Élémentaliste", "icon_id" => 30],
            ["name" => "Sage", "icon_id" => 31],
            ["name" => "Sorcier", "icon_id" => 32],
            ["name" => "Agriculteur", "icon_id" => 33],
            ["name" => "Homme des bois", "icon_id" => 34],
            ["name" => "Mineur", "icon_id" => 35],
            ["name" => "Archéologue", "icon_id" => 36],
            ["name" => "Fruit", "icon_id" => 37],
            ["name" => "Bois", "icon_id" => 38],
            ["name" => "Eau", "icon_id" => 39],
            ["name" => "Grain", "icon_id" => 40],
            ["name" => "Métal", "icon_id" => 41],
            ["name" => "Plante", "icon_id" => 42],
            ["name" => "Pierre de lune", "icon_id" => 43],
            ["name" => "Cuir", "icon_id" => 44],
            ["name" => "Mana sauvage bleu", "icon_id" => 45],
            ["name" => "Mana sauvage vert", "icon_id" => 46],
            ["name" => "Mana sauvage rouge", "icon_id" => 47],
            ["name" => "Mana sauvage gris", "icon_id" => 48],
            ["name" => "Mana sauvage blanc", "icon_id" => 49],
            ["name" => "Diplomate", "icon_id" => 50],
            ["name" => "Santé", "icon_id" => 51],
        ]);
    }
}
