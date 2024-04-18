<?php

declare(strict_types=1);

return Camelot\CsFixer\Config::create()
    ->addRules(
        Camelot\CsFixer\Rules::create()
            ->risky()
            ->php83()
            ->phpUnit100()
    )
    ->addRules([
        '@PhpCsFixer:risky' => true,
        'php_unit_test_class_requires_covers' => false,
        'ordered_traits' => false,
    ])
    ->in('src')
;
