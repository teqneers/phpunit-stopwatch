<?php

declare(strict_types=1);

/**
 * Copyright (c) 2024-2026 TEQneers GmbH & Co. KG
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/teqneers/phpunit-stopwatch
 */

namespace TQ\Testing\Extension\Stopwatch\Test;

use PHPUnit\Framework\TestCase;
use TQ\Testing\Extension\Stopwatch\Stopwatch;

/**
 * Pins the guarantee that production code may call Stopwatch::start()/stop()
 * unconditionally: when the extension never bootstrapped a collector, the calls
 * must be safe no-ops rather than throwing.
 */
final class StopwatchWithoutCollectorTest extends TestCase
{
    protected function setUp(): void
    {
        // Simulate "extension not loaded": no collector has been configured.
        self::collectorProperty()->setValue(null, null);
    }

    public function testStartIsSafeNoOpWithoutCollector(): void
    {
        Stopwatch::start('db-query');

        // reaching this line proves no exception was thrown; the assertion proves
        // start() did not lazily create any collector state either
        self::assertNull(self::collectorProperty()->getValue());
    }

    public function testStopIsSafeNoOpWithoutCollector(): void
    {
        // both the default (throwing) and the silent variants must be safe
        Stopwatch::stop('db-query');
        Stopwatch::stop('db-query', true);

        self::assertNull(self::collectorProperty()->getValue());
    }

    private static function collectorProperty(): \ReflectionProperty
    {
        return new \ReflectionProperty(Stopwatch::class, 'collector');
    }
}
