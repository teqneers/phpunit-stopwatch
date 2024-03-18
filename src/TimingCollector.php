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

namespace TQ\Testing\Extension\Stopwatch;

use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\Clock;
use TQ\Testing\Extension\Stopwatch\Exception\StopwatchException;

final class TimingCollector
{
    private array $timing      = [];
    private array $totalTiming = [];

    public function __construct(
        private readonly ClockInterface $clock = new Clock(),
    ) {
    }

    public function reset(?string $name = null): void
    {
        if (null !== $name) {
            unset($this->timing[$name]);
        } else {
            $this->timing = [];
        }
    }

    public function start(string $name): void
    {
        $time = (float)$this->clock->now()->format('U.u');

        // only start timing if it was not started yet
        if (!isset($this->timing[$name])) {
            $this->timing[$name] = [
                'start'    => $time,
                'end'      => null,
                'duration' => null,
                // time means how many times the stopwatch for $name was used
                'times' => 0,
            ];

            if (!isset($this->totalTiming[$name])) {
                $this->totalTiming[$name] = [
                    'start'    => $time,
                    'end'      => null,
                    'duration' => null,
                    // time means how many times the stopwatch for $name was used
                    'times' => 0,
                ];
            }
        }
    }

    public function stop(string $name, bool $silent = false): void
    {
        $time = (float)$this->clock->now()->format('U.u');

        if (!isset($this->timing[$name])) {
            if ($silent) {
                return;
            }

            throw new StopwatchException("Stopwatch {$name} not started");
        }

        $duration                   = $time - $this->timing[$name]['start'];
        $this->timing[$name]['end'] = $time;
        $this->timing[$name]['duration'] += $duration;
        ++$this->timing[$name]['times'];

        $this->totalTiming[$name]['end'] = $time;
        $this->totalTiming[$name]['duration'] += $duration;
        ++$this->totalTiming[$name]['times'];
    }

    public function isStarted(string $name): bool
    {
        return isset($this->timing[$name]);
    }

    public function getTiming(?string $name = null): array
    {
        if (null !== $name) {
            if (!isset($this->timing[$name])) {
                throw new StopwatchException("Stopwatch {$name} not started");
            }

            return $this->timing[$name];
        }

        return $this->timing;
    }

    public function getTotalTiming(?string $name = null): array
    {
        if (null !== $name) {
            if (!isset($this->totalTiming[$name])) {
                throw new StopwatchException("Stopwatch {$name} not started");
            }

            return $this->totalTiming[$name];
        }

        return $this->totalTiming;
    }
}
