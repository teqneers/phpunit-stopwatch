<?php

namespace TQ\Testing\Extension\Stopwatch\Subscriber;

use PHPUnit\Event\Test\PreparationStartedSubscriber;
use TQ\Testing\Extension\Stopwatch\TimingCollector;

class ResetForTest implements PreparationStartedSubscriber
{
    public function __construct(
        private readonly TimingCollector $stopwatch
    ) {
    }

    public function notify($event): void
    {
        $this->stopwatch->reset();
//        var_dump('Stopwatch reseted');
    }
}
