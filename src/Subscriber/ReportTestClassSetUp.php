<?php

namespace TQ\Testing\Extension\Stopwatch\Subscriber;

use PHPUnit\Event\Test\BeforeFirstTestMethodFinished;
use PHPUnit\Event\Test\BeforeFirstTestMethodFinishedSubscriber;
use TQ\Testing\Extension\Stopwatch\Reporter\Reporter;
use TQ\Testing\Extension\Stopwatch\TimingCollector;

class ReportTestClassSetUp implements BeforeFirstTestMethodFinishedSubscriber
{
    public function __construct(
        private readonly TimingCollector $stopwatch,
        private readonly Reporter $reporter
    ) {
    }

    public function notify(BeforeFirstTestMethodFinished $event): void
    {
        echo $this->reporter->report(
            "Stopwatch for {$event->testClassName()} SetUp",
            $this->stopwatch->getStopWatchTotal(),
            $this->stopwatch->getStopWatch()
        );
    }
}
