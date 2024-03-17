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

namespace TQ\Testing\Extension\Stopwatch\Reporter;

/**
 * @internal
 */
final class DefaultReporter implements Reporter
{
    public function report(string $headline, array $totals, ?array $current = null): string
    {
        $output    = '';
        $nameWidth = 50;

        if (null !== $current) {
            if (!empty($current)) {
                $output .= "\n\n{$headline}:\n";

                /** @var array $stopWatch */
                foreach ($current as $name => $stopWatch) {
                    /** @var array $total */
                    $total = $totals[$name];

                    $printName = \strlen($name) > 50 ? '...' . \substr($name, -46) : $name;
                    $output .= \sprintf(
                        "- %-{$nameWidth}s %-s TOTAL %-s\n",
                        $printName,
                        $this->measureString($stopWatch),
                        $this->measureString($total),
                    );
                }
            }

            return $output;
        }

        $nameWidth += 34;

        if (!empty($totals)) {
            $output .= "\n\nStopwatch TOTALS:\n";

            /** @var array $total */
            foreach ($totals as $name => $total) {
                $printName = \strlen($name) > 50 ? '...' . \substr($name, -46) : $name;
                $output .= \sprintf(
                    "- %-{$nameWidth}s TOTAL %-s\n",
                    $printName,
                    $this->measureString($total),
                );
            }
        }

        return $output;
    }

    private function measureString(array $dataPoint): string
    {
        return \sprintf(
            '%10.3fsecs (%5dx, Ø %6.2f)',
            $dataPoint['duration'],
            $dataPoint['times'],
            0 < $dataPoint['times'] ? $dataPoint['duration'] / $dataPoint['times'] : '-',
        );
    }
}
