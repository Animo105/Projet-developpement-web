<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IconSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('icons')->insert([
            ["name" => "Mana Bleu", "path" => "game-icons:drop"],
            ["name" => "Mana Vert", "path" => "game-icons:falling-leaf"],
            ["name" => "Mana Rouge", "path" => "game-icons:flamer"],
            ["name" => "Mana Gris", "path" => "game-icons:moon"],
            ["name" => "Mana Blanc", "path" => "game-icons:sun"],
            ["name" => "Mana Générique", "path" => ""],
            ["name" => "Mana Partagé", "path" => ""],
            ["name" => "Fabricant", "path" => "game-icons:anvil-impact"],
            ["name" => "Combattant", "path" => "game-icons:crossed-swords"],
            ["name" => "Érudit", "path" => "game-icons:spell-book"],
            ["name" => "Concocteur", "path" => "game-icons:potion-ball"],
            ["name" => "Récolteur", "path" => "game-icons:basket"],
            ["name" => "Tacticien", "path" => ""],
            ["name" => "Chevalier", "path" => ""],
            ["name" => "Rodeur", "path" => ""],
            ["name" => "Barbare", "path" => ""],
            ["name" => "Moine", "path" => ""],
            ["name" => "Savant", "path" => ""],
            ["name" => "Médecin", "path" => ""],
            ["name" => "Herbaliste", "path" => ""],
            ["name" => "Alchemiste", "path" => ""],
            ["name" => "Infuseur", "path" => ""],
            ["name" => "Enchanteur", "path" => ""],
            ["name" => "Aubergiste", "path" => ""],
            ["name" => "Charpentier", "path" => ""],
            ["name" => "Forgeron", "path" => ""],
            ["name" => "Joailler", "path" => ""],
            ["name" => "Magicien", "path" => ""],
            ["name" => "Druide", "path" => ""],
            ["name" => "Élémentaliste", "path" => ""],
            ["name" => "Sage", "path" => ""],
            ["name" => "Sorcier", "path" => ""],
            ["name" => "Agriculteur", "path" => ""],
            ["name" => "Homme des bois", "path" => ""],
            ["name" => "Mineur", "path" => ""],
            ["name" => "Archéologue", "path" => ""],
            ["name" => "Fruit", "path" => "game-icons:grapes"],
            ["name" => "Bois", "path" => "game-icons:log"],
            ["name" => "Eau", "path" => "game-icons:waterskin"],
            ["name" => "Grain", "path" => "game-icons:grain"],
            ["name" => "Métal", "path" => "game-icons:metal-bar"],
            ["name" => "Plante", "path" => "game-icons:new-shoot"],
            ["name" => "Pierre de lune", "path" => "game-icons:mineral-pearls"],
            ["name" => "Cuir", "path" => "game-icons:animal-hide"],
            ["name" => "Mana sauvage bleu", "path" => "game-icons:drop"],
            ["name" => "Mana sauvage vert", "path" => "game-icons:falling-leaf"],
            ["name" => "Mana sauvage rouge", "path" => "game-icons:flamer"],
            ["name" => "Mana sauvage gris", "path" => "game-icons:moon"],
            ["name" => "Mana sauvage blanc", "path" => "game-icons:sun"],
            ["name" => "Diplomat", "path" => ""],
            ["name" => "Santé", "path" => "game-icons:hearts"],
        ]);
    }
}
