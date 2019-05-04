<?php

var_dump((new \Test\RepositoryDependingOnTypeChecks\SomeClass())->aMethod(123));
var_dump((new \Test\RepositoryIndirectlyDependingOnTypeChecks\SomeClass())->aMethod(123));
var_dump((new \Test\RepositoryNotDependingOnTypeChecks\SomeClass())->aMethod(123));
