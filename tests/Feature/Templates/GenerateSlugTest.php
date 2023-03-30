<?php

use MailCarrier\Actions\Templates\GenerateSlug;
use MailCarrier\Models\Template;

it('generates slug without conflicts', function () {
    expect(GenerateSlug::resolve()->run('Foo, Bar'))->toBe('foo-bar');
});

it('generates slug with conflicts appending a counter', function () {
    Template::factory()->create([
        'slug' => 'foo-bar',
    ]);

    $newSlug = GenerateSlug::resolve()->run('Foo, Bar');
    expect($newSlug)->toBe('foo-bar-1');

    // Create template to trigger the logic of another slug conflict
    Template::factory()->create([
        'slug' => $newSlug,
    ]);

    expect(GenerateSlug::resolve()->run('Foo, Bar'))->toBe('foo-bar-2');
});
