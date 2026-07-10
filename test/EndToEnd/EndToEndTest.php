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

namespace TQ\Testing\Extension\Stopwatch\Test\EndToEnd;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Runs a real PHPUnit process with the Stopwatch extension enabled against the
 * StopwatchScenario fixture and asserts that the extension bootstraps and emits
 * both its per-test and total reports.
 *
 * This is the only test that actually exercises Extension::bootstrap() and the
 * real PHPUnit event dispatch/ordering — neither can be reached from a unit test
 * (Configuration cannot be constructed and subscribers cannot be registered once
 * PHPUnit has sealed its event system).
 */
#[Group('e2e')]
final class EndToEndTest extends TestCase
{
    public function testExtensionEmitsReportsUnderRealPhpunit(): void
    {
        $phpunit = \realpath(__DIR__ . '/../../vendor/bin/phpunit');
        $config  = __DIR__ . '/phpunit.xml';

        if (false === $phpunit || !\is_file($phpunit)) {
            self::markTestSkipped('phpunit binary not found');
        }

        if (!\function_exists('exec')) {
            self::markTestSkipped('exec() is disabled');
        }

        $command = \escapeshellarg(\PHP_BINARY) . ' '
            . \escapeshellarg($phpunit)
            . ' --configuration ' . \escapeshellarg($config)
            . ' --do-not-cache-result 2>&1';

        $outputLines = [];
        $exitCode    = 0;
        \exec($command, $outputLines, $exitCode);
        $output = \implode("\n", $outputLines);

        self::assertSame(0, $exitCode, "The extension-enabled PHPUnit subprocess failed:\n" . $output);

        // the extension bootstrapped and produced a per-test report for the fixture...
        self::assertStringContainsString('Stopwatch for', $output);
        self::assertStringContainsString('StopwatchScenario', $output);
        // ...measuring the user's named block...
        self::assertStringContainsString('measured-block', $output);
        // ...and the run-wide totals at the end.
        self::assertStringContainsString('Stopwatch TOTALS:', $output);
    }
}
