<?php

declare(strict_types=1);

namespace TQ\Testing\Extension\Stopwatch\Reporter;

use PHPUnit\Framework\TestStatus\Failure;

/**
 * @internal
 */
class DefaultReporter implements Reporter
{
    public function report(string $headline, array $totals, ?array $current = null): string
    {
        $output = '';
        $nameWidth = 50;
        if ($current !== null) {
            if (!empty($current)) {
                $output .= "\n\n{$headline}:\n";
                /** @var Failure $failure */
                foreach ($current as $name => $stopWatch) {
                    $total = $totals[$name];
                    $printName = strlen($name) > 50 ? '...' . substr($name, -46) : $name;
                    $output .= sprintf(
                        "- %-{$nameWidth}s %-s TOTAL %-s\n",
                        $printName,
                        $this->measureString($stopWatch),
                        $this->measureString($total)
                    );
                }
            }

            return $output;
        }

        $nameWidth += 34;
        if (!empty($totals)) {
            $output .= "\n\nStopwatch TOTALS:\n";
            /** @var Failure $failure */
            foreach ($totals as $name => $total) {
                $printName = strlen($name) > 50 ? '...' . substr($name, -46) : $name;
                $output .= sprintf(
                    "- %-{$nameWidth}s TOTAL %-s\n",
                    $printName,
                    $this->measureString($total)
                );
            }
        }

        return $output;
    }

    protected function measureString(array $dataPoint): string
    {
        return sprintf(
            "%10.3fsecs (%5dx, Ø %6.2f)",
            $dataPoint['duration'],
            $dataPoint['times'],
            $dataPoint['times'] > 0 ? $dataPoint['duration'] / $dataPoint['times'] : '-'
        );
    }
}