<?php

declare(strict_types=1);

namespace TQ\Testing\Extension\Stopwatch\Subscriber;

use PHPUnit\Event\Test\BeforeFirstTestMethodCalledSubscriber;
use TQ\Testing\Extension\Stopwatch\TimingCollector;

class ResetForTestClassSetUp implements BeforeFirstTestMethodCalledSubscriber
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
