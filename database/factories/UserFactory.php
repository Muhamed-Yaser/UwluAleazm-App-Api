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
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('123456789'),
            'phone_number' => $this->faker->phoneNumber(),
            'role' => $this->faker->randomElement(['student', 'teacher']),
            'status' => 'pending',
            'photo' => $this->faker->imageUrl(200, 200, 'people', true, 'Profile' , false , 'png'),
            'country' => $this->faker->country(),
            'language' => $this->faker->randomElement(['English', 'Arabic']),
            'job' => $this->faker->jobTitle(),
            'age' => $this->faker->numberBetween(18, 65),
            'gender' => $this->faker->randomElement(['ذكر', 'أنثى']),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
