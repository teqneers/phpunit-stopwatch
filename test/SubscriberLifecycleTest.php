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
use TQ\Testing\Extension\Stopwatch\Reporter\DefaultReporter;
use TQ\Testing\Extension\Stopwatch\Stopwatch;
use TQ\Testing\Extension\Stopwatch\Subscriber\ReportTest;
use TQ\Testing\Extension\Stopwatch\Subscriber\ReportTestClassSetUp;
use TQ\Testing\Extension\Stopwatch\Subscriber\ReportTestClassTearDown;
use TQ\Testing\Extension\Stopwatch\Subscriber\ResetForTest;
use TQ\Testing\Extension\Stopwatch\Subscriber\ResetForTestClassSetUp;
use TQ\Testing\Extension\Stopwatch\Subscriber\ResetForTestClassTearDown;
use TQ\Testing\Extension\Stopwatch\Subscriber\TestStart;
use TQ\Testing\Extension\Stopwatch\Subscriber\TestStop;
use TQ\Testing\Extension\Stopwatch\Subscriber\TotalReport;
use TQ\Testing\Extension\Stopwatch\Test\Util\Helper;
use TQ\Testing\Extension\Stopwatch\TimingCollector;

/**
 * Integration test: drives the whole subscriber set through one realistic class
 * lifecycle (class set-up, two tests, class tear-down, totals) in PHPUnit's real
 * event-emission order, and asserts the wiring, the TestStop-before-ReportTest
 * ordering, the per-test reset, and the accumulation of totals.
 */
final class SubscriberLifecycleTest extends TestCase
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

    public function testFullLifecycleWiringResetAndAccumulation(): void
    {
        $reporter = new DefaultReporter();

        // Wire every subscriber against the one collector, exactly as Extension::bootstrap() does.
        $testStart           = new TestStart($this->collector);
        $testStop            = new TestStop($this->collector);
        $resetClassSetUp     = new ResetForTestClassSetUp($this->collector);
        $resetTest           = new ResetForTest($this->collector);
        $resetClassTearDown  = new ResetForTestClassTearDown($this->collector);
        $reportClassSetUp    = new ReportTestClassSetUp($this->collector, $reporter);
        $reportClassTearDown = new ReportTestClassTearDown($this->collector, $reporter);
        $reportTest          = new ReportTest($this->collector, $reporter);
        $totalReport         = new TotalReport($this->collector, $reporter);

        // === class set-up: reset, then a measured bootstrap step ===
        $resetClassSetUp->notify(self::fakeEventBeforeFirstTestMethodCalled());
        Stopwatch::start('bootstrap');
        $this->clock->sleep(2);
        Stopwatch::stop('bootstrap');
        $setUpReport = self::capture(static fn () => $reportClassSetUp->notify(self::fakeEventTestBeforeFirstTestMethodFinished()));

        // === test 1: work runs for 3s ===
        $resetTest->notify(self::fakeEventPreparationStarted());
        $testStart->notify(self::fakeEventPrepared());
        Stopwatch::start('work');
        $this->clock->sleep(3);
        Stopwatch::stop('work');
        $finishedOne = self::fakeEventTestFinished(testMethod: self::fakeTestMethod('MyTest', 'testOne'));
        $test1Report = self::capture(static function () use ($testStop, $reportTest, $finishedOne): void {
            $testStop->notify($finishedOne);   // stops 'Test' BEFORE the per-test report reads it
            $reportTest->notify($finishedOne);
        });

        // === test 2: work runs for 5s ===
        $resetTest->notify(self::fakeEventPreparationStarted());
        $testStart->notify(self::fakeEventPrepared());
        Stopwatch::start('work');
        $this->clock->sleep(5);
        Stopwatch::stop('work');
        $finishedTwo = self::fakeEventTestFinished(testMethod: self::fakeTestMethod('MyTest', 'testTwo'));
        $test2Report = self::capture(static function () use ($testStop, $reportTest, $finishedTwo): void {
            $testStop->notify($finishedTwo);
            $reportTest->notify($finishedTwo);
        });

        // === class tear-down ===
        $tearDownReport = self::capture(static fn () => $reportClassTearDown->notify(self::fakeEventAfterLastTestMethodFinished()));
        $resetClassTearDown->notify(self::fakeEventTestSuiteFinished());

        // === totals ===
        $totalsReport = self::capture(static fn () => $totalReport->notify(self::fakeEventApplicationFinished()));

        // --- class set-up report shows the measured bootstrap step ---
        self::assertStringContainsString('Stopwatch for TestClassNameBefore SetUp', $setUpReport);
        self::assertMatchesRegularExpression('/bootstrap +2\.000secs \( *1x.*TOTAL +2\.000secs \( *1x/', $setUpReport);

        // --- test 1: 'work' and the auto 'Test' timer are both reported with the current test's
        // timing. The 'Test' row proves TestStop stops the timer BEFORE ReportTest reads it;
        // 'bootstrap' is gone from the per-test view, proving ResetForTest cleared it. ---
        self::assertStringContainsString('Stopwatch for MyTest::testOne', $test1Report);
        self::assertMatchesRegularExpression('/work +3\.000secs \( *1x.*TOTAL +3\.000secs \( *1x/', $test1Report);
        self::assertMatchesRegularExpression('/Test +3\.000secs \( *1x.*TOTAL +3\.000secs \( *1x/', $test1Report);
        self::assertStringNotContainsString('bootstrap', $test1Report);

        // --- test 2 (the crux): current 'work' is 5s/1x (per-test reset happened) while the
        // TOTAL column is 8s/2x (totals accumulate across tests). Same for the 'Test' timer. ---
        self::assertMatchesRegularExpression('/work +5\.000secs \( *1x.*TOTAL +8\.000secs \( *2x/', $test2Report);
        self::assertMatchesRegularExpression('/Test +5\.000secs \( *1x.*TOTAL +8\.000secs \( *2x/', $test2Report);

        // --- class tear-down is reported for the class ---
        self::assertStringContainsString('Stopwatch for TestClassNameAfter TearDown', $tearDownReport);

        // --- totals: 'bootstrap' survived every reset; 'work' and 'Test' accumulated over both tests ---
        self::assertStringContainsString('Stopwatch TOTALS:', $totalsReport);
        self::assertMatchesRegularExpression('/bootstrap +TOTAL +2\.000secs \( *1x/', $totalsReport);
        self::assertMatchesRegularExpression('/work +TOTAL +8\.000secs \( *2x/', $totalsReport);
        self::assertMatchesRegularExpression('/Test +TOTAL +8\.000secs \( *2x/', $totalsReport);
    }

    /**
     * @param callable():void $emit
     */
    private static function capture(callable $emit): string
    {
        \ob_start();

        try {
            $emit();
        } catch (\Throwable $throwable) {
            \ob_end_clean();

            throw $throwable;
        }

        return (string)\ob_get_clean();
    }
}
