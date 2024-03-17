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

namespace TQ\Testing\Extension\Stopwatch\Subscriber;

use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\FinishedSubscriber;
use TQ\Testing\Extension\Stopwatch\Reporter\Reporter;
use TQ\Testing\Extension\Stopwatch\TimingCollector;

final class ReportTest implements FinishedSubscriber
{
    public function __construct(
        private readonly TimingCollector $stopwatch,
        private readonly Reporter $reporter,
    ) {
    }

    public function notify(Finished $event): void
    {
        echo $this->reporter->report(
            "Stopwatch for {$event->test()->id()}",
            $this->stopwatch->getTotalTiming(),
            $this->stopwatch->getTiming(),
        );
    }
}
