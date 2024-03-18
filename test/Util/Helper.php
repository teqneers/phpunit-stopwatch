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

namespace TQ\Testing\Extension\Stopwatch\Test\Util;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Event\Application\Finished as ApplicationFinished;
use PHPUnit\Event\Code\ClassMethod;
use PHPUnit\Event\Code\Test;
use PHPUnit\Event\Code\TestCollection;
use PHPUnit\Event\Code\TestDox;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Event\Telemetry\GarbageCollectorStatus;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Telemetry\Info;
use PHPUnit\Event\Telemetry\MemoryUsage;
use PHPUnit\Event\Telemetry\Snapshot;
use PHPUnit\Event\Test\AfterLastTestMethodFinished;
use PHPUnit\Event\Test\BeforeFirstTestMethodCalled;
use PHPUnit\Event\Test\BeforeFirstTestMethodFinished;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Event\TestSuite\Finished as TestSuiteFinished;
use PHPUnit\Event\TestSuite\TestSuite;
use PHPUnit\Event\TestSuite\TestSuiteForTestClass;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\Runner\Version;

trait Helper
{
    final protected static function faker(string $locale = 'en_US'): Generator
    {
        /**
         * @var array<string, Generator> $fakers
         */
        static $fakers = [];

        if (!\array_key_exists($locale, $fakers)) {
            $faker = Factory::create($locale);

            $faker->seed(27001);

            $fakers[$locale] = $faker;
        }

        return $fakers[$locale];
    }

    final protected static function fakeTestDox(
        string $prettifiedClassName = 'PrettyClassName',
        string $prettifiedMethodName = 'PrettyMethodName',
        string $prettifiedAndColorizedMethodName = 'PrettyAndColorizedMethodName',
    ): TestDox {
        return new TestDox(
            $prettifiedClassName,
            $prettifiedMethodName,
            $prettifiedAndColorizedMethodName,
        );
    }

    final protected static function fakeTestMethod(
        string $className = 'ClassName',
        string $methodName = 'MethodName',
        string $file = 'File',
        int $line = 1,
        ?TestDox $testDox = null,
        array $metadata = [],
        array $testData = [],
    ): TestMethod {
        return new TestMethod(
            $className,
            $methodName,
            $file,
            $line,
            $testDox ?? self::fakeTestDox(),
            MetadataCollection::fromArray($metadata),
            TestDataCollection::fromArray($testData),
        );
    }

    final protected static function fakeClassMethod(
        string $className = 'ClassName',
        string $methodName = 'MethodName',
    ): ClassMethod {
        return new ClassMethod(
            $className,
            $methodName,
        );
    }

    /**
     * @psalm-param list<Test> $tests
     */
    final protected static function fakeTestSuite(
        string $name = 'TestSuite',
        int $size = 1,
        array $tests = [],
        string $file = 'TestFile',
        int $line = 1,
    ): TestSuite {
        return new TestSuiteForTestClass(
            $name,
            $size,
            TestCollection::fromArray($tests),
            $file,
            $line,
        );
    }

    final protected static function fakeGarbageCollectorStatus(
        int $runs = 0,
        int $collected = 0,
        int $threshold = 0,
        int $roots = 0,
        ?float $applicationTime = 0.0,
        ?float $collectorTime = 0.0,
        ?float $destructorTime = 0.0,
        ?float $freeTime = 0.0,
        ?bool $running = false,
        ?bool $protected = false,
        ?bool $full = true,
        ?int $bufferSize = 0,
    ): GarbageCollectorStatus {
        if (\version_compare(Version::id(), '10.3', '<')) {
            return new GarbageCollectorStatus(
                $runs,
                $collected,
                $threshold,
                $roots,
                $running,
                $protected,
                $full,
                $bufferSize,
            );
        }

        return new GarbageCollectorStatus(
            $runs,
            $collected,
            $threshold,
            $roots,
            $applicationTime,
            $collectorTime,
            $destructorTime,
            $freeTime,
            $running,
            $protected,
            $full,
            $bufferSize,
        );
    }

    final protected static function fakeTelemetrySnapshot(
        ?HRTime $time = null,
        ?MemoryUsage $memoryUsage = null,
        ?MemoryUsage $peakMemoryUsage = null,
        ?GarbageCollectorStatus $garbageCollectorStatus = null,
    ): Snapshot {
        return new Snapshot(
            $time ?? HRTime::fromSecondsAndNanoseconds(0, 0),
            $memoryUsage ?? MemoryUsage::fromBytes(0),
            $peakMemoryUsage ?? MemoryUsage::fromBytes(0),
            $garbageCollectorStatus ?? self::fakeGarbageCollectorStatus(),
        );
    }

    final protected static function fakeTelemetryInfo(
        ?Snapshot $snapshot = null,
        ?Duration $duration = null,
        ?MemoryUsage $memoryUsage = null,
        ?Duration $time = null,
        ?MemoryUsage $peakMemoryUsage = null,
    ): Info {
        return new Info(
            $snapshot ?? self::fakeTelemetrySnapshot(),
            $duration ?? Duration::fromSecondsAndNanoseconds(0, 0),
            $memoryUsage ?? MemoryUsage::fromBytes(0),
            $time ?? Duration::fromSecondsAndNanoseconds(0, 0),
            $peakMemoryUsage ?? MemoryUsage::fromBytes(0),
        );
    }

    final protected static function fakeEventTestFinished(
        ?Info $info = null,
        ?TestMethod $testMethod = null,
        int $result = 1,
    ): Finished {
        return new Finished(
            $info ?? self::fakeTelemetryInfo(),
            $testMethod ?? self::fakeTestMethod(),
            $result,
        );
    }

    final protected static function fakeEventTestBeforeFirstTestMethodFinished(
        ?Info $info = null,
        string $testClassName = 'TestClassNameBefore',
        ?ClassMethod $calledMethods = null,
    ): BeforeFirstTestMethodFinished {
        /** @psalm-var class-string $testClassName */
        return new BeforeFirstTestMethodFinished(
            $info ?? self::fakeTelemetryInfo(),
            $testClassName,
            $calledMethods ?? self::fakeClassMethod(),
        );
    }

    final protected static function fakeEventAfterLastTestMethodFinished(
        ?Info $info = null,
        string $testClassName = 'TestClassNameAfter',
        ?ClassMethod $calledMethods = null,
    ): AfterLastTestMethodFinished {
        /** @psalm-var class-string $testClassName */
        return new AfterLastTestMethodFinished(
            $info ?? self::fakeTelemetryInfo(),
            $testClassName,
            $calledMethods ?? self::fakeClassMethod(),
        );
    }

    final protected static function fakeEventApplicationFinished(
        ?Info $info = null,
        int $shellExitCode = 0,
    ): ApplicationFinished {
        return new ApplicationFinished(
            $info ?? self::fakeTelemetryInfo(),
            $shellExitCode,
        );
    }

    final protected static function fakeEventPreparationStarted(
        ?Info $info = null,
        ?Test $test = null,
    ): PreparationStarted {
        return new PreparationStarted(
            $info ?? self::fakeTelemetryInfo(),
            $test ?? self::fakeTestMethod(),
        );
    }

    final protected static function fakeEventPrepared(
        ?Info $info = null,
        ?Test $test = null,
    ): Prepared {
        return new Prepared(
            $info ?? self::fakeTelemetryInfo(),
            $test ?? self::fakeTestMethod(),
        );
    }

    final protected static function fakeEventBeforeFirstTestMethodCalled(
        ?Info $info = null,
        string $testClassName = 'TestClassNameBeforeFirst',
        ?ClassMethod $calledMethod = null,
    ): BeforeFirstTestMethodCalled {
        /** @psalm-var class-string $testClassName */
        return new BeforeFirstTestMethodCalled(
            $info ?? self::fakeTelemetryInfo(),
            $testClassName,
            $calledMethod ?? self::fakeClassMethod(),
        );
    }

    final protected static function fakeEventTestSuiteFinished(
        ?Info $info = null,
        ?TestSuite $testSuite = null,
    ): TestSuiteFinished {
        return new TestSuiteFinished(
            $info ?? self::fakeTelemetryInfo(),
            $testSuite ?? self::fakeTestSuite(),
        );
    }
}
