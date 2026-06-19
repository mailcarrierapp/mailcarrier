<?php

namespace MailCarrier\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use MailCarrier\Actions\Templates\GenerateSlug;
use MailCarrier\Models\Template;

#[Description('Create a new email template. The content uses the Twig templating syntax. If no slug is provided, one will be generated automatically from the name.')]
class CreateTemplateTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request, GenerateSlug $generateSlug): ResponseFactory
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:templates,slug'],
            'description' => ['nullable', 'string'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:255'],
            'layout_id' => ['nullable', 'integer', 'exists:layouts,id'],
        ]);

        $template = Template::query()->create([
            'user_id' => $request->user()?->getAuthIdentifier(),
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? $generateSlug->run($validated['name']),
            'content' => $validated['content'],
            'description' => $validated['description'] ?? null,
            'tags' => $validated['tags'] ?? null,
            'layout_id' => $validated['layout_id'] ?? null,
        ]);

        return Response::structured([
            'id' => $template->id,
            'slug' => $template->slug,
            'name' => $template->name,
            'message' => 'Template created successfully.',
        ]);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()
                ->description('The internal name of the template.')
                ->required(),

            'content' => $schema->string()
                ->description('The Twig content of the template.')
                ->required(),

            'slug' => $schema->string()
                ->description('A unique identifier used as the "template" key when sending emails. Leave empty to auto-generate from the name.'),

            'description' => $schema->string()
                ->description('A short description of the template, visible only in the admin area.'),

            'tags' => $schema->array()
                ->items($schema->string())
                ->description('A list of tags to categorize the template.'),

            'layout_id' => $schema->integer()
                ->description('The id of an existing layout the template should extend.'),
        ];
    }
}
