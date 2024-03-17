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

namespace TQ\Testing\Extension\Stopwatch\Subscriber;

use PHPUnit\Event\TestSuite\FinishedSubscriber;
use TQ\Testing\Extension\Stopwatch\TimingCollector;

final class ResetForTestClassTearDown implements FinishedSubscriber
{
    public function __construct(
        private readonly TimingCollector $stopwatch,
    ) {
    }

    public function notify(\PHPUnit\Event\TestSuite\Finished $event): void
    {
        $this->stopwatch->reset();
    }
}
