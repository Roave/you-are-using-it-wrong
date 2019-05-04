<?php

declare(strict_types=1);

use Test\RepositoryDependingOnTypeChecks\SomeClass;

var_dump((new SomeClass())->aMethod(123));
var_dump((new \Test\RepositoryIndirectlyDependingOnTypeChecks\SomeClass())->aMethod(123));
var_dump((new \Test\RepositoryNotDependingOnTypeChecks\SomeClass())->aMethod(123));
