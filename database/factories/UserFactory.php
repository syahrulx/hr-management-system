<?php

namespace Database\Factories;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'national_id' => fake()->unique()->numerify('2#############'),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'bank_acc_no' => fake()->iban(),
            'email_verified_at' => now(),
            'password' => '$2y$10$R3ByxXtWwnuJfmTmPf.LfOxt2h5xDF2GmdCuoRJDOgMqVWZwkOGjK',
            'remember_token' => Str::random(10),
            'hired_on' => Carbon::createFromTimestamp(mt_rand(Carbon::now()->subYears(3)->timestamp, Carbon::now()->timestamp))->format('Y-m-d'),
            'userRole' => 'employee',
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}


