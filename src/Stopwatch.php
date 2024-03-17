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

namespace TQ\Testing\Extension\Stopwatch;

final class Stopwatch
{
    private static ?TimingCollector $collector = null;

    /**
     * @psalm-suppress UnusedConstructor
     */
    private function __construct()
    {
        // static class
    }

    public static function init(TimingCollector $collector): void
    {
        self::$collector = $collector;
    }

    public static function start(string $name): void
    {
        // ignore if already started
        if (!self::$collector || self::$collector->isStarted($name)) {
            return;
        }

        self::$collector->start($name);
    }

    public static function stop(string $name, bool $force = false): void
    {
        if (!self::$collector) {
            return;
        }

        self::$collector->stop($name, $force);
    }
}
