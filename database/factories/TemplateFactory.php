<?php

namespace MailCarrier\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MailCarrier\Models\Layout;
use MailCarrier\Models\Template;

class TemplateFactory extends Factory
{
    protected $model = Template::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'layout_id' => Layout::factory(),
            'is_locked' => false,
            'is_active' => true,
            'name' => $this->faker->word(),
            'slug' => $this->faker->unique()->slug(),
            'content' => $this->faker->randomHtml(),
        ];
    }
}
