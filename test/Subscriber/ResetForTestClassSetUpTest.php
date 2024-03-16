<?php

declare(strict_types=1);

namespace TQ\Testing\Extension\Stopwatch\Test\Subscriber;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use TQ\Testing\Extension\Stopwatch\Reporter\DefaultReporter;
use TQ\Testing\Extension\Stopwatch\Stopwatch;
use TQ\Testing\Extension\Stopwatch\Subscriber\ResetForTestClassSetUp;
use TQ\Testing\Extension\Stopwatch\Test\Util\Helper;
use TQ\Testing\Extension\Stopwatch\TimingCollector;

class ResetForTestClassSetUpTest extends TestCase
{
    use Helper;

    private MockClock       $clock;
    private TimingCollector $collector;
    private DefaultReporter $reporter;

    public function setUp(): void
    {
        $this->clock = new MockClock();
        $this->clock->modify('2024-01-01 00:00:00');

        $this->reporter  = new DefaultReporter();
        $this->collector = new TimingCollector($this->clock);
        Stopwatch::init($this->collector);
    }

    public function testReset(): void
    {
        $name = 'Test';
        Stopwatch::start($name);
        $this->clock->sleep(10);
        Stopwatch::stop($name);
        $this->clock->sleep(10);

        $timing = $this->collector->getTiming($name);
        self::assertIsArray($timing);
        self::assertEquals(1704067200.0, $timing['start']);
        self::assertEquals(1704067210.0, $timing['end']);
        self::assertEquals(10, $timing['duration']);
        self::assertEquals(1, $timing['times']);

        $reportTest = new ResetForTestClassSetUp($this->collector);
        $reportTest->notify(self::fakeEventBeforeFirstTestMethodCalled());

        Stopwatch::start($name);
        $this->clock->sleep(10);
        Stopwatch::stop($name);

        $timing = $this->collector->getTiming($name);
        self::assertIsArray($timing);
        self::assertEquals(1704067220.0, $timing['start']);
        self::assertEquals(1704067230.0, $timing['end']);
        self::assertEquals(10, $timing['duration']);
        self::assertEquals(1, $timing['times']);
    }
}
