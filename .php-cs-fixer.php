<?php

declare(strict_types=1);

/**
 * Copyright (c) 2024 TEQneers GmbH & Co. KG
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/teqneers/phpunit-stopwatch
 */

use Ergebnis\License;
use Ergebnis\PhpCsFixer;

$license = License\Type\MIT::markdown(
    __DIR__ . '/LICENSE.md',
    License\Range::since(
        License\Year::fromString('2024'),
        new DateTimeZone('UTC'),
    ),
    License\Holder::fromString('TEQneers GmbH & Co. KG'),
    License\Url::fromString('https://github.com/teqneers/phpunit-stopwatch'),
);

$license->save();

$ruleSet = PhpCsFixer\Config\RuleSet\Php81::create()
    ->withHeader($license->header())
    ->withRules(
        PhpCsFixer\Config\Rules::fromArray([
            'mb_str_functions'       => false,
            'cast_spaces'            => ['space' => 'none'],
            'binary_operator_spaces' => ['default' => 'align_single_space_minimal'],
        ]),
    );

$config = PhpCsFixer\Config\Factory::fromRuleSet($ruleSet);

$config->getFinder()
    ->exclude([
        '.build/',
        '.github/',
    ])
    ->ignoreDotFiles(false)
    ->in(__DIR__);

$config->setCacheFile(__DIR__ . '/.build/php-cs-fixer/.php-cs-fixer.cache');

return $config;
