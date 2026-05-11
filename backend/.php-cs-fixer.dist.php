<?php

declare(strict_types=1);

/**
 * PHP CS Fixer — Symfony-aligned rules for this API Platform app.
 *
 * @see https://cs.symfony.com/doc/ruleSets/Symfony.html
 * With the dev stack up (`docker compose up` from the repo root):
 *   docker compose exec backend composer cs-fix
 *   docker compose exec backend composer cs-fixer:check
 */
$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__.'/bin',
        __DIR__.'/config',
        __DIR__.'/migrations',
        __DIR__.'/public',
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->notPath('bundles.php')
    ->notPath('reference.php')
    ->name('*.php');

return (new PhpCsFixer\Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PHP83Migration' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        // PHPUnit-specific rules (no @PHPUnitSymfony ruleset in all Fixer versions)
        'php_unit_construct' => true,
        'php_unit_dedicate_assert' => true,
        'php_unit_method_casing' => true,
        'php_unit_test_case_static_method_calls' => ['call_type' => 'self'],
    ])
    ->setFinder($finder);
