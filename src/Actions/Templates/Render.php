<?php

namespace MailCarrier\Actions\Templates;

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
            $mainFileName => $mainFileContent,
            $layoutFileName => $template->layout?->content,
        ]));

        return $latte->renderToString($mainFileName, $variables);
    }
}
