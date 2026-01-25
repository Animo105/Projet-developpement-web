<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Character>
 */
class CharacterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->userName(),
            'lore' => $this->faker->paragraph,
            'skill_point' => $this->faker->numberBetween(0, 10),
            'knowledge_point' => $this->faker->numberBetween(0, 10),
        ];
    }
}
