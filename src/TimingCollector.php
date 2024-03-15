<?php

declare(strict_types=1);

namespace TQ\Testing\Extension\Stopwatch;

class TimingCollector
{
    protected array $stopWatch = [];

    protected array $stopWatchTotal = [];

    public function reset(string $name = null): void
    {
        if ($name !== null) {
            unset($this->stopWatch[$name]);
        } else {
            $this->stopWatch = [];
        }
    }

    public function start(string $name): void
    {
        $time = microtime(true);
        if (!isset($this->stopWatch[$name])) {
            $this->stopWatch[$name] = [
                'start'    => $time,
                'end'      => null,
                'duration' => null,
                // time means how many times the stopwatch for $name was used
                'times'    => 0,
            ];
            if (!isset($this->stopWatchTotal[$name])) {
                $this->stopWatchTotal[$name] = [
                    'start'    => $time,
                    'end'      => null,
                    'duration' => null,
                    // time means how many times the stopwatch for $name was used
                    'times'    => 0,
                ];
            }
        } else {
            $this->stopWatch[$name]['start']      = $time;
            $this->stopWatchTotal[$name]['start'] = $time;
        }
    }

    public function stop(string $name, bool $silent = false): void
    {
        $time = microtime(true);
        if (!isset($this->stopWatch[$name])) {
            if ($silent) {
                return;
            }
            throw new \Exception("Stopwatch $name not started");
        }
        $duration = $time - $this->stopWatch[$name]['start'];

        $this->stopWatch[$name]['end']      = $time;
        $this->stopWatch[$name]['duration'] += $duration;
        ++$this->stopWatch[$name]['times'];

        $this->stopWatchTotal[$name]['end']      = $time;
        $this->stopWatchTotal[$name]['duration'] += $duration;
        ++$this->stopWatchTotal[$name]['times'];
    }

    public function getStopWatch(string $name = null): array
    {
        if ($name !== null) {
            if (!isset($this->stopWatch[$name])) {
                throw new \Exception("Stopwatch $name not started");
            }

            return $this->stopWatch[$name];
        }

        return $this->stopWatch;
    }

    public function getStopWatchTotal(string $name = null): array
    {
        if ($name !== null) {
            if (!isset($this->stopWatchTotal[$name])) {
                throw new \Exception("Stopwatch $name not started");
            }

            return $this->stopWatchTotal[$name];
        }

        return $this->stopWatchTotal;
    }


}
