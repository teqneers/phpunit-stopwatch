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

namespace TQ\Testing\Extension\Stopwatch\Reporter;

/**
 * @internal
 *
 * @psalm-import-type TimingData from \TQ\Testing\Extension\Stopwatch\TimingCollector
 * @psalm-import-type TimingMap from \TQ\Testing\Extension\Stopwatch\TimingCollector
 */
final class DefaultReporter implements Reporter
{
    /**
     * Width of the name column in the per-test report.
     */
    private const NAME_WIDTH = 50;

    /**
     * Width of the name column in the totals-only report. It is wider than
     * {@see self::NAME_WIDTH} because that report omits the per-test timing column.
     */
    private const TOTALS_NAME_WIDTH = self::NAME_WIDTH + 34;

    /**
     * Names longer than this are truncated with a leading ellipsis.
     */
    private const MAX_NAME_LENGTH = self::NAME_WIDTH;

    /**
     * Number of trailing characters kept from a truncated name (after the ellipsis).
     */
    private const TRUNCATED_TAIL = self::NAME_WIDTH - 4;
    private const ELLIPSIS       = '...';

    /**
     * @param TimingMap      $totals
     * @param null|TimingMap $current
     */
    public function report(string $headline, array $totals, ?array $current = null): string
    {
        $output = '';

        if (null !== $current) {
            if (!empty($current)) {
                $output .= "\n\n{$headline}:\n";

                foreach ($current as $name => $stopWatch) {
                    $total = $totals[$name];

                    $output .= \sprintf(
                        '- %-' . self::NAME_WIDTH . "s %-s TOTAL %-s\n",
                        self::truncateName($name),
                        self::measureString($stopWatch),
                        self::measureString($total),
                    );
                }
            }

            return $output;
        }

        if (!empty($totals)) {
            $output .= "\n\nStopwatch TOTALS:\n";

            foreach ($totals as $name => $total) {
                $output .= \sprintf(
                    '- %-' . self::TOTALS_NAME_WIDTH . "s TOTAL %-s\n",
                    self::truncateName($name),
                    self::measureString($total),
                );
            }
        }

        return $output;
    }

    private static function truncateName(string $name): string
    {
        return \strlen($name) > self::MAX_NAME_LENGTH
            ? self::ELLIPSIS . \substr($name, -self::TRUNCATED_TAIL)
            : $name;
    }

    /**
     * @param TimingData $dataPoint
     */
    private static function measureString(array $dataPoint): string
    {
        $times    = $dataPoint['times'];
        $duration = $dataPoint['duration'] ?? 0.0;

        // Build the average as a string so the "-" fallback (used when a timer was
        // started but never stopped) is not coerced to 0.00 by a %f specifier.
        $average = 0 < $times
            ? \sprintf('%6.2f', $duration / (float)$times)
            : \sprintf('%6s', '-');

        return \sprintf('%10.3fsecs (%5dx, Ø %s)', $duration, $times, $average);
    }
}
