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

namespace TQ\Testing\Extension\Stopwatch\Test;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use TQ\Testing\Extension\Stopwatch\Exception\StopwatchException;
use TQ\Testing\Extension\Stopwatch\Stopwatch;
use TQ\Testing\Extension\Stopwatch\Test\Util\Helper;
use TQ\Testing\Extension\Stopwatch\TimingCollector;

final class StopwatchTest extends TestCase
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

    public function testStart(): void
    {
        $name = self::faker()->word();
        Stopwatch::start($name);

        $timing = $this->collector->getTiming($name);
        self::assertEquals(1704067200.0, $timing['start']);
        self::assertNull($timing['end']);
        self::assertNull($timing['duration']);
        self::assertEquals(0, $timing['times']);

        // total timing should be the same if no reset has been triggered
        $totalTiming = $this->collector->getTiming($name);
        self::assertEquals($totalTiming, $timing);
    }

    /**
     * It is expected that the start time is not updated if the stopwatch is started twice without stopping it
     */
    public function testUnstoppedRestart(): void
    {
        $name = self::faker()->word();
        Stopwatch::start($name);

        $timing = $this->collector->getTiming($name);
        self::assertEquals(1704067200.0, $timing['start']);
        self::assertNull($timing['end']);
        self::assertNull($timing['duration']);
        self::assertEquals(0, $timing['times']);

        // start again
        $this->clock->sleep(10);
        Stopwatch::start($name);

        $timing = $this->collector->getTiming($name);
        self::assertEquals(1704067200.0, $timing['start']);
        self::assertNull($timing['end']);
        self::assertNull($timing['duration']);
        self::assertEquals(0, $timing['times']);

        // total timing should be the same if no reset has been triggered
        $totalTiming = $this->collector->getTiming($name);
        self::assertEquals($totalTiming, $timing);
    }

    public function testStopAndDuration(): void
    {
        $name = self::faker()->word();
        Stopwatch::start($name);

        $timing = $this->collector->getTiming($name);
        self::assertEquals(1704067200.0, $timing['start']);
        self::assertNull($timing['end']);
        self::assertNull($timing['duration']);
        self::assertEquals(0, $timing['times']);

        // start again
        $this->clock->sleep(10);
        Stopwatch::stop($name);

        $timing = $this->collector->getTiming($name);
        self::assertEquals(1704067200.0, $timing['start']);
        self::assertEquals(1704067210.0, $timing['end']);
        self::assertEquals(10, $timing['duration']);
        self::assertEquals(1, $timing['times']);

        // total timing should be the same if no reset has been triggered
        $totalTiming = $this->collector->getTiming($name);
        self::assertEquals($totalTiming, $timing);
    }

    /**
     * Test if multiple start/stop cycles are counted correctly.
     */
    public function testTimes(): void
    {
        $name = self::faker()->word();
        Stopwatch::start($name);

        $timing = $this->collector->getTiming($name);
        self::assertEquals(1704067200.0, $timing['start']);
        self::assertNull($timing['end']);
        self::assertNull($timing['duration']);
        self::assertEquals(0, $timing['times']);

        // start again
        Stopwatch::stop($name);
        Stopwatch::start($name);
        Stopwatch::stop($name);
        Stopwatch::start($name);
        Stopwatch::stop($name);

        $timing = $this->collector->getTiming($name);
        self::assertEquals(1704067200.0, $timing['start']);
        self::assertEquals(1704067200.0, $timing['end']);
        self::assertEquals(0.0, $timing['duration']);
        self::assertEquals(3, $timing['times']);

        // total timing should be the same if no reset has been triggered
        $totalTiming = $this->collector->getTiming($name);
        self::assertEquals($totalTiming, $timing);
    }

    /**
     * A reset should only reset the current timing, not the total timing.
     */
    public function testReset(): void
    {
        $name = self::faker()->word();
        Stopwatch::start($name);

        $timing = $this->collector->getTiming($name);
        self::assertEquals(1704067200.0, $timing['start']);
        self::assertNull($timing['end']);
        self::assertNull($timing['duration']);
        self::assertEquals(0, $timing['times']);

        // start again
        $this->clock->sleep(10);
        Stopwatch::stop($name);
        $this->collector->reset($name);

        $this->clock->sleep(10);
        Stopwatch::start($name);
        $this->clock->sleep(10);
        Stopwatch::stop($name);
        $this->collector->reset($name);

        $this->clock->sleep(10);
        Stopwatch::start($name);
        $this->clock->sleep(10);
        Stopwatch::stop($name);

        $timing = $this->collector->getTiming($name);
        self::assertEquals(1704067240.0, $timing['start']);
        self::assertEquals(1704067250.0, $timing['end']);
        self::assertEquals(10.0, $timing['duration']);
        self::assertEquals(1, $timing['times']);

        // total should be accumulated and not reset
        $totalTiming = $this->collector->getTotalTiming($name);
        self::assertEquals(1704067200.0, $totalTiming['start']);
        self::assertEquals(1704067250.0, $totalTiming['end']);
        self::assertEquals(30.0, $totalTiming['duration']);
        self::assertEquals(3, $totalTiming['times']);
    }

    public function testStopWithoutStartException(): void
    {
        $this->expectException(StopwatchException::class);

        $name = self::faker()->word();
        Stopwatch::stop($name);
    }

    public function testTimingWithoutStopException(): void
    {
        $this->expectException(StopwatchException::class);

        $name = self::faker()->word();
        $this->collector->getTiming($name);
    }

    public function testTotalTimingWithoutStopException(): void
    {
        $this->expectException(StopwatchException::class);

        $name = self::faker()->word();
        $this->collector->getTotalTiming($name);
    }

    public function testWrongNameException(): void
    {
        $this->expectException(StopwatchException::class);

        Stopwatch::start('foo');
        $this->collector->getTotalTiming('bar');
    }
}
