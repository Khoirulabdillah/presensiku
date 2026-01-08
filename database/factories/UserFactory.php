<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
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
        // Use the factory-provided faker when available. If it's not present (production without dev deps),
        // provide a minimal fallback object so seeding doesn't fail.
        $faker = $this->faker ?? new class {
            public function name()
            {
                return 'User ' . substr(md5(uniqid('', true)), 0, 6);
            }
            public function userName()
            {
                return 'user' . rand(1000, 9999);
            }
        };

        return [
            'name' => $faker->name(),
            'username' => $faker->userName(),
            'password' => Hash::make('password'),
            'role' => 'user',
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [

        ]);
    }
}
