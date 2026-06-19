<?php

namespace MailCarrier\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsOpenWorld;
use MailCarrier\Actions\SendMail;
use MailCarrier\Dto\SendMailDto;
use MailCarrier\Models\Template;

#[Description('Send a test email rendered from an existing template to a single recipient. Useful to verify how a template looks once delivered. The email is not stored in the logs.')]
#[IsOpenWorld]
class SendTestEmailTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request, SendMail $sendMail): Response
    {
        $validated = $request->validate([
            'slug' => ['required_without:id', 'nullable', 'string', 'max:255'],
            'id' => ['required_without:slug', 'nullable', 'integer'],
            'email' => ['required', 'email'],
            'subject' => ['nullable', 'string', 'max:255'],
            'variables' => ['nullable', 'array'],
            'enqueue' => ['nullable', 'boolean'],
        ], [
            'slug.required_without' => 'You must provide either a "slug" or an "id" to identify the template.',
            'id.required_without' => 'You must provide either a "slug" or an "id" to identify the template.',
        ]);

        try {
            $template = isset($validated['id'])
                ? Template::query()->where('id', $validated['id'])->firstOrFail()
                : Template::query()->where('slug', $validated['slug'])->firstOrFail();
        } catch (ModelNotFoundException) {
            return Response::error('No template found matching the given slug or id.');
        }

        try {
            $sendMail
                ->withoutLogging()
                ->run(
                    new SendMailDto(
                        template: $template->slug,
                        subject: $validated['subject'] ?? 'Test from ' . Config::string('app.name'),
                        recipient: $validated['email'],
                        enqueue: $validated['enqueue'] ?? false,
                        variables: Arr::undot($validated['variables'] ?? []),
                    )
                );
        } catch (\Exception $e) {
            return Response::error('Failed to send the test email: ' . $e->getMessage());
        }

        return Response::text(sprintf(
            'Test email for template "%s" %s %s.',
            $template->slug,
            ($validated['enqueue'] ?? false) ? 'queued for' : 'sent to',
            $validated['email'],
        ));
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'slug' => $schema->string()
                ->description('The slug of the template to send. Use this or "id".'),

            'id' => $schema->integer()
                ->description('The id of the template to send. Use this or "slug".'),

            'email' => $schema->string()
                ->format('email')
                ->description('The recipient email address of the test email.')
                ->required(),

            'subject' => $schema->string()
                ->description('The subject of the test email. Defaults to "Test from <app name>".'),

            'variables' => $schema->object()
                ->description('Key-value variables to inject into the template while rendering.'),

            'enqueue' => $schema->boolean()
                ->description('Whether to queue the email instead of sending it synchronously.'),
        ];
    }
}
