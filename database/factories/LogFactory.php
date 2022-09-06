<?php

namespace MailCarrier\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MailCarrier\Dto\ContactDto;
use MailCarrier\Dto\LogTemplateDto;
use MailCarrier\Enums\LogStatus;
use MailCarrier\Models\Log;
use MailCarrier\Models\Template;

class LogFactory extends Factory
{
    protected $model = Log::class;

    public function definition(): array
    {
        /** @var Template */
        $template = Template::factory()->create();

        return [
            'template_id' => $template->id,
            'status' => $this->faker->randomElement(LogStatus::toValues()),
            'trigger' => $this->faker->word(),
            'subject' => $this->faker->sentence(),
            'recipient' => $this->faker->safeEmail(),
            'error' => null,
            'sender' => new ContactDto(
                email: $this->faker->safeEmail(),
                name: $this->faker->name(),
            ),
            'template_frozen' => new LogTemplateDto(
                name: $template->name,
                render: $template->content,
                hash: $template->getHash(),
            ),
            'variables' => [
                'name' => $this->faker->name(),
            ],
            'cc' => new ContactDto(
                email: $this->faker->safeEmail(),
                name: $this->faker->name(),
            ),
            'bcc' => new ContactDto(
                email: $this->faker->safeEmail(),
                name: $this->faker->name(),
            ),
        ];
    }
}
