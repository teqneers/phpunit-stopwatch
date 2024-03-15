<?php

namespace TQ\Testing\Extension\Stopwatch\Subscriber;

use PHPUnit\Event\Test\BeforeFirstTestMethodCalledSubscriber;
use TQ\Testing\Extension\Stopwatch\Stopwatch;

class ResetForTestClassSetUp implements BeforeFirstTestMethodCalledSubscriber
{
    public function __construct(
        private readonly Stopwatch $stopwatch
    ) {
    }

    public function notify($event): void
    {
        $this->stopwatch->reset();
    }
}
