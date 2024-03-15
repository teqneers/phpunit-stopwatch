<?php

namespace TQ\Testing\Extension\Stopwatch\Subscriber;

use PHPUnit\Event\Application\FinishedSubscriber;
use TQ\Testing\Extension\Stopwatch\Reporter\Reporter;
use TQ\Testing\Extension\Stopwatch\TimingCollector;

class TotalReport implements FinishedSubscriber
{
    public function __construct(
        private readonly TimingCollector $stopwatch,
        private readonly Reporter $reporter
    ) {
    }

    public function notify($event): void
    {
        echo $this->reporter->report("Stopwatch TOTALS", $this->stopwatch->getStopWatchTotal());
    }
}
