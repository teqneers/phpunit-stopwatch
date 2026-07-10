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
 * @psalm-import-type TimingMap from \TQ\Testing\Extension\Stopwatch\TimingCollector
 */
interface Reporter
{
    /**
     * @param TimingMap      $totals
     * @param null|TimingMap $current
     */
    public function report(string $headline, array $totals, ?array $current = null): string;
}
