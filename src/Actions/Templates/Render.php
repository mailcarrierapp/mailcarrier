<?php

namespace MailCarrier\Actions\Templates;

use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Latte;
use MailCarrier\Actions\Action;
use MailCarrier\Models\Template;

class Render extends Action
{
    /**
     * Render a template with the given variables.
     */
    public function run(Template $template, array $variables = []): string
    {
        $mainFileName = sprintf('main-%d.latte', $template->id);
        $layoutFileName = !$template->layout_id ? null : sprintf('layout-%d.latte', $template->layout_id);
        $mainFileContent = !$template->layout_id ? $template->content : sprintf(
            "{layout '%s'}{block content}%s{/block}",
            $layoutFileName,
            $template->content,
        );

        $latte = new Latte\Engine();
        $latte->setLoader(new Latte\Loaders\StringLoader([
            $mainFileName => $this->parse($mainFileContent),
            $layoutFileName => $this->parse($template->layout?->content ?: ''),
        ]));

        return $latte->renderToString($mainFileName, $variables);
    }

    /**
     * Parse the template syntax.
     */
    protected function parse(string $template): string
    {
        preg_match_all('/{{(.*?)}}/', $template, $variables);

        foreach ($variables[1] ?? [] as $variable) {
            $realVariable = Str::of($variable)
                ->before('|')
                ->trim()
                ->toString();

            // Extract filters to reapply them when replacing the variable
            $variableFilters = '';

            if (str_contains($variable, '|')) {
                $variableFilters = Str::of($variable)
                    ->after('|')
                    ->trim()
                    ->whenNotEmpty(fn (Stringable $str) => $str->prepend('|'))
                    ->toString();
            }

            // Transform variable if is an array/object
            if (str_contains($realVariable, '.')) {
                // Transform everything after the actual variable name into an array-based index
                $indexBasedVariable = Str::of($realVariable)
                    ->after('.')
                    ->explode('.')
                    ->map(fn (string $value) => is_numeric($value) ? "[$value]" : "['$value']")
                    ->join('');

                // Prepend the actual variable name
                $realVariable = Str::before($realVariable, '.') . $indexBasedVariable;
            }

            if (!str_starts_with($realVariable, '$')) {
                $realVariable = '$' . $realVariable;
            }

            $template = str_replace(
                '{{' . $variable . '}}',
                '{' . $realVariable . $variableFilters . '}',
                $template
            );
        }

        return $template;
    }
}
