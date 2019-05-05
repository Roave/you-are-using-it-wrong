<?php

declare(strict_types=1);

namespace My\AwesomeLibrary;

final class MyHelloWorld
{
    /** @param array<string> $people */
    public static function sayHello(array $people) : string
    {
        return 'Hello ' . implode(', ', $people) . '!';
    }
}
