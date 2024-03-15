<?php

namespace TQ\Testing\Extension\Stopwatch\Subscriber;

use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\FinishedSubscriber;
use TQ\Testing\Extension\Stopwatch\Reporter\Reporter;
use TQ\Testing\Extension\Stopwatch\Stopwatch;

class ReportTest implements FinishedSubscriber
{
    public function __construct(
        private readonly Stopwatch $stopwatch,
        private readonly Reporter $reporter)
    {
    }

    public function notify(Finished $event): void
    {
        echo $this->reporter->report("Stopwatch for {$event->test()->id()}", $this->stopwatch->getStopWatchTotal(),
            $this->stopwatch->getStopWatch());
    }

}
