<?php

declare(strict_types=1);

/**
 * Copyright (c) 2024 TEQneers GmbH & Co. KG
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/teqneers/phpunit-stopwatch
 */

namespace TQ\Testing\Extension\Stopwatch\Test\Subscriber;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use TQ\Testing\Extension\Stopwatch\Stopwatch;
use TQ\Testing\Extension\Stopwatch\Subscriber\TestStop;
use TQ\Testing\Extension\Stopwatch\Test\Util\Helper;
use TQ\Testing\Extension\Stopwatch\TimingCollector;

final class TestStopTest extends TestCase
{
    use Helper;
    private MockClock $clock;
    private TimingCollector $collector;

    protected function setUp(): void
    {
        $this->clock = new MockClock();
        $this->clock->modify('2024-01-01 00:00:00');

        $this->collector = new TimingCollector($this->clock);
        Stopwatch::init($this->collector);
    }

    public function testStopped(): void
    {
        $name = 'Test';
        Stopwatch::start($name);
        $this->clock->sleep(10);

        $reportTest = new TestStop($this->collector);
        $reportTest->notify(self::fakeEventTestFinished());

        $timing = $this->collector->getTiming($name);
        self::assertEquals(1704067200.0, $timing['start']);
        self::assertEquals(1704067210.0, $timing['end']);
        self::assertEquals(10, $timing['duration']);
        self::assertEquals(1, $timing['times']);
    }

    public function testStoppedWithoutStart(): void
    {
        $reportTest = new TestStop($this->collector);
        $reportTest->notify(self::fakeEventTestFinished());
    }
}
