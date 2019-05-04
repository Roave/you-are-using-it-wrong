<?php

declare(strict_types=1);

namespace RoaveTest\YouAreUsingItWrong\Composer;

use Composer\Package\RootPackageInterface;
use PHPUnit\Framework\TestCase;
use Roave\YouAreUsingItWrong\Composer\Package;
use Roave\YouAreUsingItWrong\Composer\PackageAutoload;

/**
 * @covers \Roave\YouAreUsingItWrong\Composer\Package
 *
 * @uses \Roave\YouAreUsingItWrong\Composer\PackageAutoload
 */
final class PackageTest extends TestCase
{
    /** @dataProvider dependencyCombinationsThatRequireStrictChecks */
    public function testRequiresStrictChecks(
        bool $expected,
        string ...$dependencies
    ) : void {
        self::assertSame(
            $expected,
            Package::fromPackageDefinition(
                [
                    'name'    => 'foo/bar',
                    'require' => array_combine($dependencies, $dependencies),
                ],
                __DIR__
            )
                   ->requiresStrictChecks()
        );
    }

    public function dependencyCombinationsThatRequireStrictChecks() : array
    {
        return [
            [false, 'aaa/bbb'],
            [true, 'roave/enforce-type-checks'],
            [true, 'aaa/bbb', 'roave/enforce-type-checks'],
            [false, 'roave/potato'],
        ];
    }

    public function testName() : void
    {
        self::assertSame(
            'foo/bar',
            Package::fromPackageDefinition(
                ['name' => 'foo/bar'],
                __DIR__
            )
                   ->name()
        );
    }

    public function testAutoload() : void
    {
        self::assertEquals(
            PackageAutoload::fromAutoloadDefinition(
                [
                    'psr-0' => ['Foo_' => 'bar'],
                ],
                __DIR__
            ),
            Package::fromPackageDefinition(
                [
                    'name'     => 'foo/bar',
                    'autoload' => [
                        'psr-0' => ['Foo_' => 'bar'],
                    ],
                ],
                __DIR__
            )
                   ->autoload()
        );
    }
}
