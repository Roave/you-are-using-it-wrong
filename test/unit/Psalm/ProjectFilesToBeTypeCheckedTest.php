<?php

declare(strict_types=1);

namespace RoaveTest\YouAreUsingItWrong\Psalm;

use PHPUnit\Framework\TestCase;
use Psalm\Config\ProjectFileFilter;
use Psalm\Issue\ArgumentIssue;
use Psalm\Issue\ClassIssue;
use Psalm\Issue\CodeIssue;
use Psalm\Issue\FunctionIssue;
use Psalm\Issue\MethodIssue;
use Psalm\Issue\PropertyIssue;
use Roave\YouAreUsingItWrong\Composer\PackageAutoload;
use Roave\YouAreUsingItWrong\Psalm\Configuration;
use Roave\YouAreUsingItWrong\Psalm\ProjectFilesToBeTypeChecked;

/** @covers \Roave\YouAreUsingItWrong\Psalm\ProjectFilesToBeTypeChecked */
final class ProjectFilesToBeTypeCheckedTest extends TestCase
{
    public function testFilesToBeChecked() : void
    {
        $files = ProjectFilesToBeTypeChecked::fromAutoloadDefinitions(
            PackageAutoload::fromAutoloadDefinition(
                [
                    'psr-0'    => ['Composer'],
                    'classmap' => ['Psalm/ProjectFilesToBeTypeCheckedTest.php'],
                ],
                __DIR__ . '/..'
            )
        );

        $reflectionInclusive = new \ReflectionProperty(ProjectFilesToBeTypeChecked::class, 'inclusive');

        $reflectionInclusive->setAccessible(true);

        self::assertSame([realpath(__DIR__ . '/../Composer') . '/'], $files->getDirectories());
        self::assertSame([realpath(__FILE__)], $files->getFiles());
        self::assertTrue($reflectionInclusive->getValue($files));
    }
}
