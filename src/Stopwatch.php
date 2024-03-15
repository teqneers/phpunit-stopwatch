<?php

namespace TQ\Testing\Extension\Stopwatch;

final class Stopwatch
{
    protected static ?TimingCollector $collector = null;

    private function __construct()
    {
        // static class
    }

    public static function init(TimingCollector $collector): void
    {
        self::$collector = $collector;
    }

    public static function start(string $name): void
    {
        if (!self::$collector) {
            return;
        }
        self::$collector->start($name);
    }

    public static function stop(string $name, bool $force = false): void
    {
        if (!self::$collector) {
            return;
        }
        self::$collector->stop($name, $force);
    }
}

