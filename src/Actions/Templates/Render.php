<?php

namespace MailCarrier\Actions\Templates;

use MailCarrier\Actions\Action;
use MailCarrier\Models\Template;

class Render extends Action
{
    /**
     * Render a template with the given variables.
     */
    public function run(Template $template, array $variables = []): string
    {
        $mainFileName = sprintf('main-%d.html', $template->id);
        $layoutFileName = !$template->layout_id ? null : sprintf('layout-%d.html', $template->layout_id);
        $mainFileContent = !$template->layout_id ? $template->content : sprintf(
            '{%% extends "%s" %%}{%% block content %%}%s{%% endblock %%}',
            $layoutFileName,
            $template->content,
        );

        $loader = new \Twig\Loader\ArrayLoader([
            $mainFileName => $mainFileContent,
            $layoutFileName => $template->layout?->content,
        ]);

        return (new \Twig\Environment($loader))->render($mainFileName, $variables);
    }
}
