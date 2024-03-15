<?php

namespace TQ\Testing\Extension\Stopwatch\Subscriber;

use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\PreparedSubscriber;
use TQ\Testing\Extension\Stopwatch\Stopwatch;

class TestStart implements PreparedSubscriber
{
    public function __construct(
        private readonly Stopwatch $stopwatch
    ) {
    }

    public function notify(Prepared $event): void
    {
        $this->stopwatch->start('Test');
    }

}
