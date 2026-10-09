<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
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
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'activo' => true,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the user is a superadministrator.
     */
    public function superadmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_SUPERADMIN,
        ]);
    }

    /**
     * Indicate that the user is a standard administrator.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_ADMIN,
        ]);
    }

    /**
     * Indicate that the user is a content editor.
     */
    public function editor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_EDITOR,
        ]);
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
