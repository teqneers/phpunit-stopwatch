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

namespace TQ\Testing\Extension\Stopwatch\Test\EndToEnd\Fixture;

use PHPUnit\Framework\TestCase;
use TQ\Testing\Extension\Stopwatch\Stopwatch;

/**
 * Fixture exercised by EndToEndTest through a real PHPUnit subprocess with the
 * extension enabled. Deliberately NOT named *Test so the main test suite (which
 * globs test/ for *Test.php) does not pick it up; the end-to-end phpunit.xml
 * references it explicitly.
 */
final class StopwatchScenario extends TestCase
{
    public function testFirstMeasuredBlock(): void
    {
        Stopwatch::start('measured-block');
        $sum = \array_sum(\range(1, 1000));
        Stopwatch::stop('measured-block');

        self::assertSame(500500, $sum);
    }

    public function testSecondMeasuredBlock(): void
    {
        Stopwatch::start('measured-block');
        $sum = \array_sum(\range(1, 100));
        Stopwatch::stop('measured-block');

        self::assertSame(5050, $sum);
    }
}
