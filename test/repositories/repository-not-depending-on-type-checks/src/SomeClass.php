<?php

declare(strict_types=1);

namespace Test\RepositoryNotDependingOnTypeChecks;

final class SomeClass
{
    public function aMethod(string $foo) : void
    {
    }
}
