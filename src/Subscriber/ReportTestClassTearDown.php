<?php

namespace TQ\Testing\Extension\Stopwatch\Subscriber;

use PHPUnit\Event\Test\AfterLastTestMethodFinished;
use PHPUnit\Event\Test\AfterLastTestMethodFinishedSubscriber;
use TQ\Testing\Extension\Stopwatch\Reporter\Reporter;
use TQ\Testing\Extension\Stopwatch\Stopwatch;

class ReportTestClassTearDown implements AfterLastTestMethodFinishedSubscriber
{
    public function __construct(
        private readonly Stopwatch $stopwatch,
        readonly Reporter $reporter
    ) {
    }

    public function notify(AfterLastTestMethodFinished $event): void
    {
        echo $this->reporter->report(
            "Stopwatch for {$event->testClassName()} TearDown",
            $this->stopwatch->getStopWatchTotal(),
            $this->stopwatch->getStopWatch()
        );
    }
}
