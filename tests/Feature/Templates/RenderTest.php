<?php

use MailCarrier\Actions\Templates\Render;
use MailCarrier\Models\Layout;
use MailCarrier\Models\Template;
use Twig\Error\RuntimeError;

it('throws error if a required variable on Layout is not provided', function () {
    $template = Template::factory()->create([
        'layout_id' => Layout::factory()->create([
            'content' => 'Company {{ company }} {% block content %}{% endblock %}',
        ])->id,
        'content' => 'Hello!',
    ]);

    Render::resolve()->run($template);
})->throws(RuntimeError::class);

it('throws error if a required variable on Template is not provided', function () {
    $template = Template::factory()->create([
        'layout_id' => null,
        'content' => 'Hello {{ name }}',
    ]);

    Render::resolve()->run($template);
})->throws(RuntimeError::class);

it('renders with variables', function () {
    $template = Template::factory()->create([
        'layout_id' => Layout::factory()->create([
            'content' => 'Company {{ company }} {% block content %}{% endblock %}',
        ])->id,
        'content' => 'Hello {{ name }}!',
    ]);

    $result = Render::resolve()->run($template, [
        'company' => 'MailCarrier',
        'name' => 'Foo',
    ]);

    expect($result)->toBe('Company MailCarrier Hello Foo!');
});

it('formats numbers using the format_number filter', function () {
    $template = Template::factory()->create([
        'layout_id' => null,
        'content' => '{{ number|format_number(locale="en-US") }}',
    ]);

    $result = Render::resolve()->run($template, [
        'number' => 1234.56,
    ]);

    expect($result)->toBe('1,234.56');
});

it('formats numbers using the format_number filter with a specific locale', function () {
    $template = Template::factory()->create([
        'layout_id' => null,
        'content' => '{{ number|format_number(locale="de-DE") }}',
    ]);

    $result = Render::resolve()->run($template, [
        'number' => 1234.56,
    ]);

    expect($result)->toBe('1.234,56');
});
