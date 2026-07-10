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

namespace TQ\Testing\Extension\Stopwatch\Test\Reporter;

use PHPUnit\Framework\TestCase;
use TQ\Testing\Extension\Stopwatch\Reporter\DefaultReporter;

final class DefaultReporterTest extends TestCase
{
    /**
     * Golden master: a per-test report (current !== null) renders one row per
     * timer with the per-test measurement followed by the accumulated TOTAL.
     */
    public function testRendersPerTestReportWithCurrentAndTotalColumns(): void
    {
        $reporter = new DefaultReporter();

        $totals = [
            'TQ\\Testing\\Database::import' => [
                'start'    => 1704067200.0,
                'end'      => 1704067210.0,
                'duration' => 250.958,
                'times'    => 352,
            ],
            'Test' => [
                'start'    => 1704067200.0,
                'end'      => 1704067210.0,
                'duration' => 1041.228,
                'times'    => 70,
            ],
        ];
        $current = [
            'TQ\\Testing\\Database::import' => [
                'start'    => 1704067200.0,
                'end'      => 1704067210.0,
                'duration' => 7.889,
                'times'    => 11,
            ],
            'Test' => [
                'start'    => 1704067200.0,
                'end'      => 1704067210.0,
                'duration' => 1.428,
                'times'    => 1,
            ],
        ];

        self::assertSame(
            <<<'EOD'


Stopwatch for TQ\Tests\Example\SingleTest::testDataCalculation:
- TQ\Testing\Database::import                             7.889secs (   11x, Ø   0.72) TOTAL    250.958secs (  352x, Ø   0.71)
- Test                                                    1.428secs (    1x, Ø   1.43) TOTAL   1041.228secs (   70x, Ø  14.87)

EOD,
            $reporter->report('Stopwatch for TQ\\Tests\\Example\\SingleTest::testDataCalculation', $totals, $current),
        );
    }

    /**
     * Golden master: the summary report (current === null) prints the wider
     * name column and only the TOTAL measurement.
     */
    public function testRendersTotalsOnlyReportWhenCurrentIsNull(): void
    {
        $reporter = new DefaultReporter();

        $totals = [
            'TQ\\Testing\\Database::import' => [
                'start'    => 1704067200.0,
                'end'      => 1704067210.0,
                'duration' => 250.958,
                'times'    => 352,
            ],
            'Test' => [
                'start'    => 1704067200.0,
                'end'      => 1704067210.0,
                'duration' => 1041.228,
                'times'    => 70,
            ],
        ];
        $current = null;

        self::assertSame(
            <<<'EOD'


Stopwatch TOTALS:
- TQ\Testing\Database::import                                                          TOTAL    250.958secs (  352x, Ø   0.71)
- Test                                                                                 TOTAL   1041.228secs (   70x, Ø  14.87)

EOD,
            $reporter->report('ignored when current is null', $totals, $current),
        );
    }

    /**
     * Names longer than 50 characters are truncated to '...' + the last 46
     * characters in the per-test report.
     */
    public function testTruncatesLongNamesInPerTestReport(): void
    {
        $reporter = new DefaultReporter();

        $totals = [
            'TQ\\Application\\Domain\\Service\\ReallyLongClassNameForTruncation::someMethod' => [
                'start'    => 1704067200.0,
                'end'      => 1704067210.0,
                'duration' => 327.026,
                'times'    => 184,
            ],
        ];
        $current = [
            'TQ\\Application\\Domain\\Service\\ReallyLongClassNameForTruncation::someMethod' => [
                'start'    => 1704067200.0,
                'end'      => 1704067210.0,
                'duration' => 0.117,
                'times'    => 3,
            ],
        ];

        self::assertSame(
            <<<'EOD'


Truncation:
- ...e\ReallyLongClassNameForTruncation::someMethod       0.117secs (    3x, Ø   0.04) TOTAL    327.026secs (  184x, Ø   1.78)

EOD,
            $reporter->report('Truncation', $totals, $current),
        );
    }

    /**
     * Names longer than 50 characters are truncated in the summary report too.
     */
    public function testTruncatesLongNamesInTotalsReport(): void
    {
        $reporter = new DefaultReporter();

        $totals = [
            'TQ\\Application\\Domain\\Service\\ReallyLongClassNameForTruncation::someMethod' => [
                'start'    => 1704067200.0,
                'end'      => 1704067210.0,
                'duration' => 327.026,
                'times'    => 184,
            ],
        ];
        $current = null;

        self::assertSame(
            <<<'EOD'


Stopwatch TOTALS:
- ...e\ReallyLongClassNameForTruncation::someMethod                                    TOTAL    327.026secs (  184x, Ø   1.78)

EOD,
            $reporter->report('ignored', $totals, $current),
        );
    }

    /**
     * Golden master for a started-but-never-stopped timer (times === 0,
     * duration === null): the average renders as a right-aligned dash because no
     * meaningful average exists, and the incomplete duration renders as 0.000.
     */
    public function testRendersUnstoppedTimerWithZeroTimes(): void
    {
        $reporter = new DefaultReporter();

        $totals = [
            'Unstopped' => [
                'start'    => 1704067200.0,
                'end'      => 1704067210.0,
                'duration' => null,
                'times'    => 0,
            ],
        ];
        $current = [
            'Unstopped' => [
                'start'    => 1704067200.0,
                'end'      => 1704067210.0,
                'duration' => null,
                'times'    => 0,
            ],
        ];

        self::assertSame(
            <<<'EOD'


Zero:
- Unstopped                                               0.000secs (    0x, Ø      -) TOTAL      0.000secs (    0x, Ø      -)

EOD,
            $reporter->report('Zero', $totals, $current),
        );
    }

    /**
     * An empty per-test map short-circuits to an empty string even when totals exist.
     */
    public function testReturnsEmptyStringWhenCurrentIsEmpty(): void
    {
        $reporter = new DefaultReporter();

        $totals = [
            'Test' => [
                'start'    => 1704067200.0,
                'end'      => 1704067210.0,
                'duration' => 1.0,
                'times'    => 1,
            ],
        ];
        $current = [
        ];

        self::assertSame(
            '',
            $reporter->report('Ignored', $totals, $current),
        );
    }

    /**
     * A name of exactly 50 characters sits on the truncation boundary (`> 50`) and
     * must render in full — pins the strict `>` at DefaultReporter:35 and :55.
     */
    public function testNameOfExactlyFiftyCharactersIsNotTruncated(): void
    {
        $reporter = new DefaultReporter();
        $name     = \str_repeat('a', 50);
        $point    = [
            'start'    => 1704067200.0,
            'end'      => 1704067210.0,
            'duration' => 1.0,
            'times'    => 1,
        ];

        self::assertSame(50, \strlen($name));

        $perTest = $reporter->report('headline', [$name => $point], [$name => $point]);
        self::assertStringContainsString($name, $perTest);
        self::assertStringNotContainsString('...', $perTest);

        $totalsOnly = $reporter->report('headline', [$name => $point], null);
        self::assertStringContainsString($name, $totalsOnly);
        self::assertStringNotContainsString('...', $totalsOnly);
    }

    /**
     * An empty totals map produces an empty summary report.
     */
    public function testReturnsEmptyStringWhenTotalsAreEmpty(): void
    {
        $reporter = new DefaultReporter();

        $totals = [
        ];
        $current = null;

        self::assertSame(
            '',
            $reporter->report('Ignored', $totals, $current),
        );
    }
}
