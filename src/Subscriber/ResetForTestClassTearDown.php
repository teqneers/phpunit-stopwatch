<?php

namespace TQ\Testing\Extension\Stopwatch\Subscriber;

use PHPUnit\Event\TestSuite\FinishedSubscriber;
use TQ\Testing\Extension\Stopwatch\TimingCollector;

class ResetForTestClassTearDown implements FinishedSubscriber
{
    public function __construct(
        private readonly TimingCollector $stopwatch
    ) {
    }

    public function notify($event): void
    {
        $this->stopwatch->reset();
    }
}
