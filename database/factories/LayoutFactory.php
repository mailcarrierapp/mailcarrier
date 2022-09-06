<?php

namespace MailCarrier\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MailCarrier\Models\Layout;

class LayoutFactory extends Factory
{
    protected $model = Layout::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'is_locked' => false,
            'name' => $this->faker->word(),
            'content' => $this->faker->randomHtml(),
        ];
    }
}
