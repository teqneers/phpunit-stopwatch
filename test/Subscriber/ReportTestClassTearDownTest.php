<?php

declare(strict_types=1);

namespace TQ\Testing\Extension\Stopwatch\Test\Subscriber;

use PHPUnit\Event\Test\AfterLastTestMethodFinished;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use TQ\Testing\Extension\Stopwatch\Reporter\DefaultReporter;
use TQ\Testing\Extension\Stopwatch\Stopwatch;
use TQ\Testing\Extension\Stopwatch\Subscriber\ReportTestClassTearDown;
use TQ\Testing\Extension\Stopwatch\Test\Util\Helper;
use TQ\Testing\Extension\Stopwatch\TimingCollector;

class ReportTestClassTearDownTest extends TestCase
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

        $output = $this->notifyOutput(self::fakeEventAfterLastTestMethodFinished());

        self::assertStringContainsString('Stopwatch for TestClassNameAfter TearDown:', $output);
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

        $output = $this->notifyOutput(self::fakeEventAfterLastTestMethodFinished());

        self::assertStringContainsString('Stopwatch for TestClassNameAfter TearDown:', $output);
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

        $output = $this->notifyOutput(self::fakeEventAfterLastTestMethodFinished());

        self::assertStringContainsString('Stopwatch for TestClassNameAfter TearDown:', $output);
        self::assertMatchesRegularExpression('(- ' . $name . '\s+20\.000secs)', $output);
        self::assertStringContainsString(' 1x', $output);

        self::assertMatchesRegularExpression('(- ' . $innerName . '\s+10\.000secs)', $output);
    }

    protected function notifyOutput(AfterLastTestMethodFinished $event): string
    {
        ob_start();
        $reportTest = new ReportTestClassTearDown($this->collector, $this->reporter);
        $reportTest->notify($event);

        return ob_get_clean();
    }
}
