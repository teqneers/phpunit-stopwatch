<?php

declare(strict_types=1);

namespace TQ\Testing\Extension\Stopwatch\Test\Subscriber;

use PHPUnit\Event\Application\Finished;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use TQ\Testing\Extension\Stopwatch\Reporter\DefaultReporter;
use TQ\Testing\Extension\Stopwatch\Stopwatch;
use TQ\Testing\Extension\Stopwatch\Subscriber\TotalReport;
use TQ\Testing\Extension\Stopwatch\Test\Util\Helper;
use TQ\Testing\Extension\Stopwatch\TimingCollector;

class TotalReportTest extends TestCase
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

    public function testStart(): void
    {
        Stopwatch::start('Test\SubscriberReportTestTest::testStart');

        $output = $this->notifyOutput(self::fakeEventApplicationFinished());

        self::assertStringContainsString('Stopwatch TOTALS:', $output);
        self::assertStringContainsString('Test\\SubscriberReportTestTest::testStart', $output);
        self::assertStringContainsString(' 0.000secs', $output);
        self::assertStringContainsString(' 0x', $output);
    }

    public function testStartStop(): void
    {
        $name = self::faker()->word;
        Stopwatch::start($name);
        $this->clock->sleep(10);
        Stopwatch::stop($name);

        $output = $this->notifyOutput(self::fakeEventApplicationFinished());

        self::assertStringContainsString('Stopwatch TOTALS:', $output);
        self::assertStringContainsString('- ' . $name, $output);
        self::assertStringContainsString(' 10.000secs', $output);
        self::assertStringContainsString(' 1x', $output);
    }

    public function testMultipleStartStop(): void
    {
        $name      = self::faker()->word;
        $innerName = self::faker()->word;

        Stopwatch::start($name);
        $this->clock->sleep(10);

        Stopwatch::start($innerName);
        $this->clock->sleep(10);
        Stopwatch::stop($innerName);

        Stopwatch::stop($name);

        $output = $this->notifyOutput(self::fakeEventApplicationFinished());

        self::assertStringContainsString('Stopwatch TOTALS:', $output);
        self::assertMatchesRegularExpression('(- ' . $name . '\s+TOTAL\s+20\.000secs)', $output);
        self::assertStringContainsString(' 1x', $output);

        self::assertMatchesRegularExpression('(- ' . $innerName . '\s+TOTAL\s+10\.000secs)', $output);
    }

    public function testMultipleStartStopWithReset(): void
    {
        $name      = self::faker()->word;
        $innerName = self::faker()->word;

        Stopwatch::start($name);
        $this->clock->sleep(10);

        Stopwatch::start($innerName);
        $this->clock->sleep(10);
        Stopwatch::stop($innerName);

        Stopwatch::stop($name);

        $output = $this->notifyOutput(self::fakeEventApplicationFinished());

        self::assertStringContainsString('Stopwatch TOTALS:', $output);
        self::assertMatchesRegularExpression('(- ' . $name . '\s+TOTAL\s+20\.000secs)', $output);
        self::assertStringContainsString(' 1x', $output);

        self::assertMatchesRegularExpression('(- ' . $innerName . '\s+TOTAL\s+10\.000secs)', $output);


        // do it a second time and reset the collector before to simulate another test class run
        $this->collector->reset();

        Stopwatch::start($name);
        $this->clock->sleep(10);

        Stopwatch::start($innerName);
        $this->clock->sleep(10);
        Stopwatch::stop($innerName);

        Stopwatch::stop($name);

        // simulate another test run with different stopwatch points
        $name2      = self::faker()->word;
        $innerName2 = self::faker()->word;

        Stopwatch::start($name2);
        $this->clock->sleep(30);

        Stopwatch::start($innerName2);
        $this->clock->sleep(30);
        Stopwatch::stop($innerName2);

        Stopwatch::stop($name2);

        $output = $this->notifyOutput(self::fakeEventApplicationFinished());

        self::assertStringContainsString('Stopwatch TOTALS:', $output);
        self::assertMatchesRegularExpression('(- ' . $name . '\s+TOTAL\s+40\.000secs)', $output);
        self::assertStringContainsString(' 1x', $output);

        self::assertMatchesRegularExpression('(- ' . $innerName . '\s+TOTAL\s+20\.000secs)', $output);

        self::assertMatchesRegularExpression('(- ' . $name2 . '\s+TOTAL\s+60\.000secs)', $output);
        self::assertStringContainsString(' 1x', $output);

        self::assertMatchesRegularExpression('(- ' . $innerName2 . '\s+TOTAL\s+30\.000secs)', $output);
    }

    protected function notifyOutput(Finished $event): string
    {
        ob_start();
        $reportTest = new TotalReport($this->collector, $this->reporter);
        $reportTest->notify($event);

        return ob_get_clean();
    }
}
