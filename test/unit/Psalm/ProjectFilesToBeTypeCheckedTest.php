<?php

declare(strict_types=1);

namespace RoaveTest\YouAreUsingItWrong\Psalm;

use PHPUnit\Framework\TestCase;
use Psalm\Config\ProjectFileFilter;
use ReflectionProperty;
use Roave\YouAreUsingItWrong\Composer\PackageAutoload;
use Roave\YouAreUsingItWrong\Psalm\ProjectFilesToBeTypeChecked;

use function realpath;

/**
 * @uses \Roave\YouAreUsingItWrong\Composer\PackageAutoload
 *
 * @covers \Roave\YouAreUsingItWrong\Psalm\ProjectFilesToBeTypeChecked
 */
final class ProjectFilesToBeTypeCheckedTest extends TestCase
{
    public function testFilesToBeChecked(): void
    {
        $files = ProjectFilesToBeTypeChecked::fromAutoloadDefinitions(
            PackageAutoload::fromAutoloadDefinition(
                [
                    'psr-0'    => ['Foo_' => 'Composer'],
                    'classmap' => ['Psalm/ProjectFilesToBeTypeCheckedTest.php'],
                ],
                __DIR__ . '/..',
            ),
        );

        $reflectionInclusive = new ReflectionProperty(ProjectFileFilter::class, 'inclusive');

        self::assertSame([realpath(__DIR__ . '/../Composer') . '/'], $files->getDirectories());
        self::assertSame([realpath(__FILE__)], $files->getFiles());
        self::assertTrue($reflectionInclusive->getValue($files));
    }
}
