<?php

declare(strict_types=1);

namespace TQ\Testing\Extension\Stopwatch;

use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\Clock;
use TQ\Testing\Extension\Stopwatch\Exception\StopwatchException;

class TimingCollector
{
    protected array $timing = [];

    protected array $totalTiming = [];

    public function __construct(
        private readonly ClockInterface $clock = new Clock()
    ) {
    }

    public function reset(string $name = null): void
    {
        if ($name !== null) {
            unset($this->timing[$name]);
        } else {
            $this->timing = [];
        }
    }

    public function start(string $name): void
    {
        $time = (float)$this->clock->now()->format('U.u');
        if (!isset($this->timing[$name])) {
            $this->timing[$name] = [
                'start'    => $time,
                'end'      => null,
                'duration' => null,
                // time means how many times the stopwatch for $name was used
                'times'    => 0,
            ];
            if (!isset($this->totalTiming[$name])) {
                $this->totalTiming[$name] = [
                    'start'    => $time,
                    'end'      => null,
                    'duration' => null,
                    // time means how many times the stopwatch for $name was used
                    'times'    => 0,
                ];
            }
        } else {
            /** @psalm-suppress MixedArrayAssignment */
            $this->timing[$name]['start']      = $time;
            /** @psalm-suppress MixedArrayAssignment */
            $this->totalTiming[$name]['start'] = $time;
        }
    }

    public function stop(string $name, bool $silent = false): void
    {
        $time = (float)$this->clock->now()->format('U.u');
        if (!isset($this->timing[$name])) {
            if ($silent) {
                return;
            }
            throw new StopwatchException("Stopwatch $name not started");
        }
        /** @psalm-suppress MixedArrayAccess, MixedAssignment, MixedOperand */
        $duration = $time - $this->timing[$name]['start'];

        /** @psalm-suppress MixedArrayAssignment */
        $this->timing[$name]['end'] = $time;
        /** @psalm-suppress MixedArrayAccess, MixedArrayAssignment, MixedOperand */
        $this->timing[$name]['duration'] += $duration;
        /** @psalm-suppress MixedArrayAccess, MixedArrayAssignment, MixedOperand */
        ++$this->timing[$name]['times'];

        /** @psalm-suppress MixedArrayAssignment */
        $this->totalTiming[$name]['end']      = $time;
        /** @psalm-suppress MixedArrayAccess, MixedArrayAssignment, MixedOperand */
        $this->totalTiming[$name]['duration'] += $duration;
        /** @psalm-suppress MixedArrayAccess, MixedArrayAssignment, MixedOperand */
        ++$this->totalTiming[$name]['times'];
    }

    public function isStarted(string $name): bool
    {
        return isset($this->timing[$name]);
    }

    /**
     * @psalm-suppress MixedInferredReturnType
     */
    public function getTiming(string $name = null): array
    {
        if ($name !== null) {
            if (!isset($this->timing[$name])) {
                throw new StopwatchException("Stopwatch $name not started");
            }

            /** @psalm-suppress MixedReturnStatement */
            return $this->timing[$name];
        }

        return $this->timing;
    }

    /**
     * @psalm-suppress MixedInferredReturnType
     */
    public function getTotalTiming(string $name = null): array
    {
        if ($name !== null) {
            if (!isset($this->totalTiming[$name])) {
                throw new StopwatchException("Stopwatch $name not started");
            }

            /** @psalm-suppress MixedReturnStatement */
            return $this->totalTiming[$name];
        }

        return $this->totalTiming;
    }


}