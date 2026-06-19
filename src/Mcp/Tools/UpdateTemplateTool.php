<?php

namespace MailCarrier\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use MailCarrier\Models\Template;

#[Description('Edit an existing email template identified by its slug or id. Only the provided fields are updated. Locked templates cannot be edited.')]
class UpdateTemplateTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response|ResponseFactory
    {
        $identifier = $request->validate([
            'slug' => ['required_without:id', 'nullable', 'string', 'max:255'],
            'id' => ['required_without:slug', 'nullable', 'integer'],
        ], [
            'slug.required_without' => 'You must provide either a "slug" or an "id" to identify the template.',
            'id.required_without' => 'You must provide either a "slug" or an "id" to identify the template.',
        ]);

        try {
            $template = isset($identifier['id'])
                ? Template::query()->where('id', $identifier['id'])->firstOrFail()
                : Template::query()->where('slug', $identifier['slug'])->firstOrFail();
        } catch (ModelNotFoundException) {
            return Response::error('No template found matching the given slug or id.');
        }

        if ($template->is_locked) {
            return Response::error('This template is locked and cannot be edited.');
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'content' => ['sometimes', 'string'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('templates', 'slug')->ignore($template->id)],
            'description' => ['sometimes', 'nullable', 'string'],
            'tags' => ['sometimes', 'nullable', 'array'],
            'tags.*' => ['string', 'max:255'],
            'layout_id' => ['sometimes', 'nullable', 'integer', 'exists:layouts,id'],
        ]);

        $template
            ->fill($validated)
            ->save();

        return Response::structured([
            'id' => $template->id,
            'slug' => $template->slug,
            'name' => $template->name,
            'message' => 'Template updated successfully.',
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
                ->description('The current slug of the template to edit. Use this or "id".'),

            'id' => $schema->integer()
                ->description('The id of the template to edit. Use this or "slug".'),

            'name' => $schema->string()
                ->description('The new internal name of the template.'),

            'content' => $schema->string()
                ->description('The new Twig content of the template.'),

            'description' => $schema->string()
                ->description('The new description of the template.'),

            'tags' => $schema->array()
                ->items($schema->string())
                ->description('The new list of tags. Replaces the existing tags.'),

            'layout_id' => $schema->integer()
                ->description('The id of an existing layout the template should extend.'),
        ];
    }
}
