<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Participant>
 */
class ParticipantFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'family_name' => $this->faker->lastName,
            'given_name'  => $this->faker->firstName,
            'birthdate'   => $this->faker->date(),
            'condition'   => null,
            'password' => static::$password ??= 'password',
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function ($participant) {
            $character = \App\Models\Character::factory()->create([
                'participant_id' => $participant->id,
            ]);

            $participant->character_id = $character->id;
            $participant->save();
        });
    }

}
