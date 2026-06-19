<?php

namespace MailCarrier\Mcp\Tools;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use MailCarrier\Models\Template;

#[Description('List the existing email templates, optionally filtered by a search term or tag. Returns a lightweight overview of each template without its content.')]
#[IsReadOnly]
#[IsIdempotent]
class ListTemplatesTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): ResponseFactory
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'tag' => ['nullable', 'string', 'max:255'],
        ]);

        $templates = Template::query()
            ->when(
                !empty($validated['search']),
                fn (Builder $query) => $query->where(function (Builder $query) use ($validated): void {
                    $query
                        ->where('name', 'like', '%' . $validated['search'] . '%')
                        ->orWhere('slug', 'like', '%' . $validated['search'] . '%');
                })
            )
            ->when(
                !empty($validated['tag']),
                fn (Builder $query) => $query->whereJsonContains('tags', $validated['tag'])
            )
            ->orderBy('name')
            ->get()
            ->map(fn (Template $template): array => [
                'id' => $template->id,
                'slug' => $template->slug,
                'name' => $template->name,
                'description' => $template->description,
                'tags' => $template->tags ?? [],
                'layout' => $template->layout?->name,
                'is_locked' => $template->is_locked,
            ])
            ->all();

        return Response::structured([
            'count' => count($templates),
            'templates' => $templates,
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
            'search' => $schema->string()
                ->description('Filter templates whose name or slug contains this term.'),

            'tag' => $schema->string()
                ->description('Only return templates that have this tag.'),
        ];
    }
}
