<?php

declare(strict_types=1);

namespace RoaveTest\YouAreUsingItWrong\Composer;

use PHPUnit\Framework\TestCase;
use Roave\YouAreUsingItWrong\Composer\Package;
use Roave\YouAreUsingItWrong\Composer\PackageAutoload;

/**
 * @uses \Roave\YouAreUsingItWrong\Composer\PackageAutoload
 *
 * @covers \Roave\YouAreUsingItWrong\Composer\Package
 */
final class PackageTest extends TestCase
{
    /**
     * @psalm-param array<non-empty-string, non-empty-string> $dependencies
     *
     * @dataProvider dependencyCombinationsThatRequireStrictChecks
     */
    public function testRequiresStrictChecks(
        bool $expected,
        array $dependencies,
    ): void {
        self::assertSame(
            $expected,
            Package::fromPackageDefinition(
                [
                    'name'    => 'foo/bar',
                    'require' => $dependencies,
                ],
                __DIR__,
            )
                   ->requiresStrictChecks(),
        );
    }

    /**
     * @return array<int, bool|string>
     * @psalm-return array<int, array{bool, array<non-empty-string, non-empty-string>}>
     */
    public static function dependencyCombinationsThatRequireStrictChecks(): array
    {
        return [
            [
                false,
                ['aaa/bbb' => '1.2.3'],
            ],
            [
                true,
                ['roave/you-are-using-it-wrong' => '4.5.6'],
            ],
            [
                true,
                [
                    'aaa/bbb' => '1.2.3',
                    'roave/you-are-using-it-wrong' => '4.5.6',
                ],
            ],
            [
                false,
                ['roave/potato' => '7.8.9'],
            ],
        ];
    }

    public function testWillNotRequiresStrictChecksIfNoDependenciesAreSet(): void
    {
        self::assertFalse(
            Package::fromPackageDefinition(['name' => 'foo/bar'], __DIR__)
                ->requiresStrictChecks(),
        );
    }

    public function testName(): void
    {
        self::assertSame(
            'foo/bar',
            Package::fromPackageDefinition(
                ['name' => 'foo/bar'],
                __DIR__,
            )
                   ->name(),
        );
    }

    public function testAutoload(): void
    {
        self::assertEquals(
            PackageAutoload::fromAutoloadDefinition(
                [
                    'psr-0' => ['Foo_' => 'bar'],
                ],
                __DIR__,
            ),
            Package::fromPackageDefinition(
                [
                    'name'     => 'foo/bar',
                    'autoload' => [
                        'psr-0' => ['Foo_' => 'bar'],
                    ],
                ],
                __DIR__,
            )
                   ->autoload(),
        );
    }
}
