<?php

use MailCarrier\Helpers\TemplateManager;
use MailCarrier\Models\Layout;
use MailCarrier\Models\Template;

it('returns empty array if template has no variables', function () {
    $template = new Template([
        'content' => 'Hello',
    ]);

    $output = TemplateManager::make($template)->extractVariableNames();

    expect($output)->toBe([]);
});

it('returns all the variables of the template', function () {
    $template = new Template([
        'content' => <<<'TWIG'
            Hello {{ name }},
            <a href="{{ ctaUrl }}">
                Sign in
            </a>

            {% if isPremium|default(false) %}
                {{ tierLevel|title }}
            {% endif %}
        TWIG,
    ]);

    $output = TemplateManager::make($template)->extractVariableNames();

    expect($output)->toBe(['name', 'ctaUrl', 'isPremium', 'tierLevel']);
});

it('returns all the variables of the template along with its layout', function () {
    $template = new Template([
        'content' => <<<'TWIG'
            Hello {{ name }},
            <a href="{{ ctaUrl }}">
                Sign in
            </a>

            {% if isPremium|default(false) %}
                {{ tierLevel|title }}
            {% endif %}
        TWIG,
    ]);

    $template->setRelation('layout', new Layout([
        'content' => <<<'TWIG'
            <!doctype html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                </head>
                <body>
                    <h1>{{ headline }}</h1>
                    {% block content %}{% endblock %}
                </body>
            </html>
        TWIG,
    ]));

    $output = TemplateManager::make($template)->extractVariableNames();

    expect($output)->toBe(['headline', 'name', 'ctaUrl', 'isPremium', 'tierLevel']);
});

it('returns empty array for a template with errors', function () {
    $template = new Template([
        'content' => <<<'TWIG'
            Hello {{ name }},
            {% if isPremium|default(false) %%}
                {{ tierLevel|title }}
        TWIG,
    ]);

    $output = TemplateManager::make($template)->extractVariableNames();

    expect($output)->toBe([]);
});
