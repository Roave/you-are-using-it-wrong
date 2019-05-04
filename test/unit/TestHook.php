<?php

declare(strict_types=1);

namespace RoaveTest\YouAreUsingItWrong;

use PHPUnit\Framework\TestCase;
use Roave\YouAreUsingItWrong\Hook;
use function array_keys;

/** @covers \Roave\YouAreUsingItWrong\Hook */
final class TestHook extends TestCase
{
    public function testSubscribedEvents() : void
    {
        $subscribers = Hook::getSubscribedEvents();

        self::assertSame(['post-install-cmd', 'post-update-cmd'], array_keys($subscribers));

        foreach ($subscribers as $subscriber) {
            self::assertIsCallable([Hook::class, $subscriber]);
        }
    }
}
