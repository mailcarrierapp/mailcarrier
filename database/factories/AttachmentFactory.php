<?php

namespace MailCarrier\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MailCarrier\Enums\AttachmentLogStrategy;
use MailCarrier\Models\Attachment;
use MailCarrier\Models\Log;

class AttachmentFactory extends Factory
{
    protected $model = Attachment::class;

    public function definition(): array
    {
        return [
            'log_id' => Log::factory(),
            'strategy' => AttachmentLogStrategy::None,
            'name' => $this->faker->slug() . '.pdf',
            'size' => $this->faker->numberBetween(1, 10000),
            'path' => $this->faker->filePath(),
            'disk' => null,
            'content' => null,
        ];
    }
}
