<?php

namespace MailCarrier\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use MailCarrier\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'oauth_id' => null,
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('secret'),
            'picture_url' => $this->faker->imageUrl(),
            'oauth_raw' => null,
        ];
    }
}
