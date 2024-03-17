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

use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\PreparedSubscriber;
use TQ\Testing\Extension\Stopwatch\TimingCollector;

final class TestStart implements PreparedSubscriber
{
    public function __construct(
        private readonly TimingCollector $stopwatch,
    ) {
    }

    public function notify(Prepared $event): void
    {
        $this->stopwatch->start('Test');
    }
}
