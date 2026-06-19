<?php

namespace MailCarrier\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use MailCarrier\Models\Template;

#[Description('Inspect a single email template by its slug or id, including its full Twig content, layout and metadata.')]
#[IsReadOnly]
#[IsIdempotent]
class GetTemplateTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'slug' => ['required_without:id', 'nullable', 'string', 'max:255'],
            'id' => ['required_without:slug', 'nullable', 'integer'],
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

        return Response::structured([
            'id' => $template->id,
            'slug' => $template->slug,
            'name' => $template->name,
            'description' => $template->description,
            'tags' => $template->tags ?? [],
            'content' => $template->content,
            'is_locked' => $template->is_locked,
            'layout' => $template->layout_id ? [
                'id' => $template->layout_id,
                'name' => $template->layout?->name,
                'content' => $template->layout?->content,
            ] : null,
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
            'slug' => $schema->string()
                ->description('The unique slug of the template to inspect.'),

            'id' => $schema->integer()
                ->description('The id of the template to inspect. Use this or "slug".'),
        ];
    }
}
