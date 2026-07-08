<?php

declare(strict_types=1);

/**
 * Copyright (c) 2024-2026 TEQneers GmbH & Co. KG
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
use TQ\Testing\Extension\Stopwatch\TimingCollector;

final class TimingCollectorTest extends TestCase
{
    private MockClock $clock;
    private TimingCollector $collector;

    protected function setUp(): void
    {
        $this->clock = new MockClock();
        $this->clock->modify('2024-01-01 00:00:00');

        $this->collector = new TimingCollector($this->clock);
    }

    /**
     * Two independently named timers must not influence each other's start,
     * end, duration or call count, even when their start/stop calls interleave.
     */
    public function testTracksMultipleNamedTimersIndependently(): void
    {
        $this->collector->start('alpha');
        $this->clock->sleep(5);
        $this->collector->start('beta');
        $this->clock->sleep(3);
        $this->collector->stop('beta');
        $this->clock->sleep(2);
        $this->collector->stop('alpha');

        $alpha = $this->collector->getTiming('alpha');
        self::assertSame(1704067200.0, $alpha['start']);
        self::assertSame(1704067210.0, $alpha['end']);
        self::assertSame(10.0, $alpha['duration']);
        self::assertSame(1, $alpha['times']);

        $beta = $this->collector->getTiming('beta');
        self::assertSame(1704067205.0, $beta['start']);
        self::assertSame(1704067208.0, $beta['end']);
        self::assertSame(3.0, $beta['duration']);
        self::assertSame(1, $beta['times']);

        // both timers coexist in the full map in insertion order
        self::assertSame(['alpha', 'beta'], \array_keys($this->collector->getTiming()));
    }

    /**
     * reset() without a name clears every current timing but must leave the
     * accumulated totals intact.
     */
    public function testResetWithoutNameClearsAllCurrentTimingsButKeepsTotals(): void
    {
        $this->collector->start('alpha');
        $this->clock->sleep(4);
        $this->collector->stop('alpha');
        $this->collector->start('beta');
        $this->clock->sleep(6);
        $this->collector->stop('beta');

        $this->collector->reset();

        self::assertFalse($this->collector->isStarted('alpha'));
        self::assertFalse($this->collector->isStarted('beta'));
        self::assertSame([], $this->collector->getTiming());

        // totals survive the reset for both timers
        self::assertSame(['alpha', 'beta'], \array_keys($this->collector->getTotalTiming()));
        self::assertSame(4.0, $this->collector->getTotalTiming('alpha')['duration']);
        self::assertSame(6.0, $this->collector->getTotalTiming('beta')['duration']);
    }

    /**
     * reset($name) removes only the named current timing and leaves other
     * timers and all totals untouched.
     */
    public function testResetWithNameClearsOnlyThatTimer(): void
    {
        $this->collector->start('alpha');
        $this->collector->start('beta');

        $this->collector->reset('alpha');

        self::assertFalse($this->collector->isStarted('alpha'));
        self::assertTrue($this->collector->isStarted('beta'));
        self::assertSame(['beta'], \array_keys($this->collector->getTiming()));

        // totals are never touched by reset()
        self::assertSame(['alpha', 'beta'], \array_keys($this->collector->getTotalTiming()));
    }

    /**
     * Resetting a name that was never started is a harmless no-op.
     */
    public function testResetOfUnknownNameIsHarmless(): void
    {
        $this->collector->start('alpha');

        $this->collector->reset('does-not-exist');

        self::assertTrue($this->collector->isStarted('alpha'));
        self::assertSame(['alpha'], \array_keys($this->collector->getTiming()));
    }

    /**
     * Stopping a never-started timer with the silent flag returns quietly and
     * records nothing (this is the path the TestStop subscriber relies on).
     */
    public function testSilentStopOfUnstartedTimerRecordsNothing(): void
    {
        $this->collector->stop('never-started', true);

        self::assertFalse($this->collector->isStarted('never-started'));
        self::assertSame([], $this->collector->getTiming());
        self::assertSame([], $this->collector->getTotalTiming());
    }

    /**
     * Stopping a never-started timer without the silent flag throws.
     */
    public function testNonSilentStopOfUnstartedTimerThrows(): void
    {
        $this->expectException(StopwatchException::class);
        $this->expectExceptionMessage('Stopwatch never-started not started');

        $this->collector->stop('never-started');
    }

    /**
     * Documents the current shape of a timing record so the upcoming value-object
     * refactor changes it deliberately rather than by accident.
     */
    public function testTimingRecordHasExpectedKeys(): void
    {
        $this->collector->start('alpha');

        self::assertSame(['start', 'end', 'duration', 'times'], \array_keys($this->collector->getTiming('alpha')));
        self::assertSame(['start', 'end', 'duration', 'times'], \array_keys($this->collector->getTotalTiming('alpha')));
    }
}
