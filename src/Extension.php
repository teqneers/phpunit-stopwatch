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

use PHPUnit\Runner;
use PHPUnit\TextUI;
use TQ\Testing\Extension\Stopwatch\Subscriber\ReportTest;
use TQ\Testing\Extension\Stopwatch\Subscriber\ReportTestClassSetUp;
use TQ\Testing\Extension\Stopwatch\Subscriber\ReportTestClassTearDown;
use TQ\Testing\Extension\Stopwatch\Subscriber\ResetForTest;
use TQ\Testing\Extension\Stopwatch\Subscriber\ResetForTestClassSetUp;
use TQ\Testing\Extension\Stopwatch\Subscriber\ResetForTestClassTearDown;
use TQ\Testing\Extension\Stopwatch\Subscriber\TestStart;
use TQ\Testing\Extension\Stopwatch\Subscriber\TestStop;
use TQ\Testing\Extension\Stopwatch\Subscriber\TotalReport;

/**
 * @psalm-api
 */
final class Extension implements Runner\Extension\Extension
{
    public function bootstrap(
        TextUI\Configuration\Configuration $configuration,
        Runner\Extension\Facade $facade,
        Runner\Extension\ParameterCollection $parameters,
    ): void {
        //        $facade->registerTracer(
        //            new class implements Tracer {
        //                public function trace(\PHPUnit\Event\Event $event): void
        //                {
        //                    $lines = explode(PHP_EOL, $event->asString());
        //                    echo "\nEVENT: ".implode(' ', $lines).PHP_EOL.PHP_EOL;
        //                }
        //            }
        //        );

        $reporter  = new Reporter\DefaultReporter();
        $stopwatch = new TimingCollector();
        Stopwatch::init($stopwatch);

        $facade->registerSubscribers(
            new TestStart($stopwatch),
            new TestStop($stopwatch),
            new ResetForTestClassSetUp($stopwatch),
            new ResetForTest($stopwatch),
            new ResetForTestClassTearDown($stopwatch),
            new ReportTestClassSetUp($stopwatch, $reporter),
            new ReportTestClassTearDown($stopwatch, $reporter),
            new ReportTest($stopwatch, $reporter),
            new TotalReport($stopwatch, $reporter),
        );
    }
}
