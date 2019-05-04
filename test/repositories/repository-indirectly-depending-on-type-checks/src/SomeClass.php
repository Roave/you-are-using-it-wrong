<?php

declare(strict_types=1);

namespace Test\RepositoryIndirectlyDependingOnTypeChecks;

final class SomeClass
{
    public function aMethod(string $foo) : void
    {
    }
}
