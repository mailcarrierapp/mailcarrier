<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . DIRECTORY_SEPARATOR . 'src')
    ->in(__DIR__ . DIRECTORY_SEPARATOR . 'config')
    ->in(__DIR__ . DIRECTORY_SEPARATOR . 'database')
    ->in(__DIR__ . DIRECTORY_SEPARATOR . 'tests')
    ->append(['.php-cs-fixer.php']);

$rules = [
    '@Symfony' => true,
    '@PSR12' => true,
    'array_syntax' => ['syntax' => 'short'],
    'concat_space' => ['spacing' => 'one'],
    'not_operator_with_space' => false,
    'increment_style' => ['style' => 'post'],
    'phpdoc_align' => false,
    'yoda_style' => false,
    'no_empty_comment' => false,
    'phpdoc_separation' => false,
    'new_with_braces' => false,
    'phpdoc_to_comment' => ['ignored_tags' => ['var']],
    'phpdoc_no_alias_tag' => false,
    'single_trait_insert_per_statement' => false,
    'no_multiline_whitespace_around_double_arrow' => false,
    'ordered_imports' => true,
];

return (new PhpCsFixer\Config())
    ->setUsingCache(true)
    ->setRules($rules)
    ->setFinder($finder);
