<?php

namespace MailCarrier\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use MailCarrier\Actions\Templates\Render;
use MailCarrier\Models\Template;

#[Description('Render an existing email template with the given variables and return the resulting HTML. Useful to preview how a template looks before sending. Does not persist anything.')]
#[IsReadOnly]
class RenderTemplateTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request, Render $render): Response
    {
        $validated = $request->validate([
            'slug' => ['required_without:id', 'nullable', 'string', 'max:255'],
            'id' => ['required_without:slug', 'nullable', 'integer'],
            'variables' => ['nullable', 'array'],
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

        $html = $render
            ->setStrictVariables(false)
            ->run($template, $validated['variables'] ?? []);

        return Response::text($html);
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
                ->description('The slug of the template to render. Use this or "id".'),

            'id' => $schema->integer()
                ->description('The id of the template to render. Use this or "slug".'),

            'variables' => $schema->object()
                ->description('Key-value variables to inject into the template while rendering.'),
        ];
    }
}
