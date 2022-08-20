<?php

use MailCarrier\Actions\Templates\Render;

it('transforms plain variables', function () {
    $result = invade(Render::resolve())->parse(<<<TEMPLATE
        {{foo}} {{\$bar}} {{foo}} {{bar}}
    TEMPLATE);

    expect($result)->toBe(<<<TEMPLATE
        {\$foo} {\$bar} {\$foo} {\$bar}
    TEMPLATE);
});

it('transforms direct access to array properties', function () {
    $result = invade(Render::resolve())->parse(<<<TEMPLATE
        {{foo['bar']}} {{\$bar['baz']}} {{foo['bar']['baz']}} {{bar['baz']}} {{bar[0]}} {{bar[0][1]}}
    TEMPLATE);

    expect($result)->toBe(<<<TEMPLATE
        {\$foo['bar']} {\$bar['baz']} {\$foo['bar']['baz']} {\$bar['baz']} {\$bar[0]} {\$bar[0][1]}
    TEMPLATE);
});

it('transforms dot notation access to array properties', function () {
    $result = invade(Render::resolve())->parse(<<<TEMPLATE
        {{foo.bar}} {{\$bar.baz}} {{foo['bar']['baz']}} {{bar.baz}} {{foo[0].bar}} {{foo.0.bar.baz}} {{foo.0.1.bar.2.baz}}
    TEMPLATE);

    expect($result)->toBe(<<<TEMPLATE
        {\$foo['bar']} {\$bar['baz']} {\$foo['bar']['baz']} {\$bar['baz']} {\$foo[0]['bar']} {\$foo[0]['bar']['baz']} {\$foo[0][1]['bar'][2]['baz']}
    TEMPLATE);
});

it('handles variables with spaces around brackets', function () {
    $result = invade(Render::resolve())->parse(<<<TEMPLATE
        {{ foo.bar }} {{ \$bar.baz }} {{ foo['bar']['baz'] }} {{foo.bar}} {{ foo }}
    TEMPLATE);

    expect($result)->toBe(<<<TEMPLATE
        {\$foo['bar']} {\$bar['baz']} {\$foo['bar']['baz']} {\$foo['bar']} {\$foo}
    TEMPLATE);
});

it('respects the engine filters', function () {
    $result = invade(Render::resolve())->parse(<<<TEMPLATE
        {{ foo.bar|upper }} {{\$bar.baz|lower}} {{foo['bar']['baz']|upper}} {{ bar|upper|reverse }} {{foo|lower|reverse}}
    TEMPLATE);

    expect($result)->toBe(<<<TEMPLATE
        {\$foo['bar']|upper} {\$bar['baz']|lower} {\$foo['bar']['baz']|upper} {\$bar|upper|reverse} {\$foo|lower|reverse}
    TEMPLATE);
});
