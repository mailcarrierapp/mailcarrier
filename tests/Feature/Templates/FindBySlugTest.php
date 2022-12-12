<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;
use MailCarrier\Actions\Templates\FindBySlug;
use MailCarrier\Models\Template;
use function Pest\Laravel\assertDatabaseCount;

it('throws exception if the model is not found', function () {
    assertDatabaseCount(Template::class, 0);

    FindBySlug::resolve()->run('foo');
})->throws(ModelNotFoundException::class);

it('returns the Template matching the slug', function () {
    /** @var Template */
    $template = Template::factory()->create([
        'slug' => 'foo',
    ]);

    Template::factory()->create([
        'slug' => 'foobar',
    ]);

    Template::factory()->create([
        'slug' => 'bar',
    ]);

    expect(FindBySlug::resolve()->run('foo')->id)->toBe($template->id);
});
