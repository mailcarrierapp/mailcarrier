<?php

namespace MailCarrier\Helpers;

use Illuminate\Support\Str;
use MailCarrier\Models\Template;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Source;

class TemplateManager
{
    public function __construct(protected Template $template)
    {
        //
    }

    public static function make(Template $template): static
    {
        return new static($template);
    }

    public static function makeFromId(string $templateId, string $content): static
    {
        return new static(
            Str::isUuid($templateId)
                ? Template::findOrFail($templateId)
                : new Template(['content' => $content])
        );
    }

    public function extractVariableNames(): array
    {
        $source = $this->template->layout?->content . $this->template->content;

        $twig = new Environment(new ArrayLoader);

        try {
            $nodes = $twig->parse(
                $twig->tokenize(new Source($source, ''))
            )->getNode('body')->getNode('0');
        } catch (\Exception) {
            return [];
        }

        preg_match_all("|Twig\\\Node\\\Expression\\\NameExpression\(name\: '(.*)'|mi", (string) $nodes, $matches);

        return array_values(array_unique($matches[1]));
    }

    protected function getLoader(): ArrayLoader
    {
        $mainFileName = sprintf('main-%d.html', $this->template->id);
        $layoutFileName = !$this->template->layout_id ? null : sprintf('layout-%d.html', $this->template->layout_id);
        $mainFileContent = !$this->template->layout_id ? $this->template->content : sprintf(
            '{%% extends "%s" %%}{%% block content %%}%s{%% endblock %%}',
            $layoutFileName,
            $this->template->content,
        );

        return new ArrayLoader([
            $mainFileName => $mainFileContent,
            $layoutFileName => $this->template->layout?->content,
        ]);
    }
}
