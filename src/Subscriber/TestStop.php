<?php

namespace TQ\Testing\Extension\Stopwatch\Subscriber;

use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\FinishedSubscriber;
use TQ\Testing\Extension\Stopwatch\Stopwatch;

class TestStop implements FinishedSubscriber
{
    public function __construct(
        private readonly Stopwatch $stopwatch
    ) {
    }

    public function notify(Finished $event): void
    {
        // not all tests are prepared and can be measured
        $this->stopwatch->stop('Test', true);
    }
}
