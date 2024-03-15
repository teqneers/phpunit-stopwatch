<?php

declare(strict_types=1);

namespace TQ\Testing\Extension\Stopwatch\Reporter;

/**
 * @internal
 */
interface Reporter
{
    public function report(string $headline, array $totals, ?array $current = null): string;
}