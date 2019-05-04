<?php

declare(strict_types=1);

namespace RoaveTest\YouAreUsingItWrong\Composer;

use Composer\Package\Locker;
use Composer\Package\RootPackageInterface;
use PHPUnit\Framework\TestCase;
use Roave\YouAreUsingItWrong\Composer\PackageAutoload;
use Roave\YouAreUsingItWrong\Composer\PackagesRequiringStrictChecks;
use Roave\YouAreUsingItWrong\Composer\Project;

/**
 * @covers \Roave\YouAreUsingItWrong\Composer\Project
 *
 * @uses \Roave\YouAreUsingItWrong\Composer\Package
 * @uses \Roave\YouAreUsingItWrong\Composer\PackageAutoload
 * @uses \Roave\YouAreUsingItWrong\Composer\PackagesRequiringStrictChecks
 */
final class ProjectTest extends TestCase
{
    public function testPackageWithoutLocalPluginInstallation() : void
    {
        $rootPackage = $this->createMock(RootPackageInterface::class);
        $locker      = $this->createMock(Locker::class);

        $locker
            ->method('getLockData')
            ->willReturn([
                'packages'     => [
                    [
                        'name'     => 'foo/bar',
                        'autoload' => [
                            'psr-4' => [
                                'Foo\\Bar\\' => ['aaa'],
                                'Foo\\Baz\\' => ['bbb'],
                            ],
                        ],
                        'require'  => [
                            'roave/enforce-type-checks' => '1.2.3',
                        ],
                    ],
                    [
                        'name'     => 'ignore/me',
                        'autoload' => [
                            'psr-4' => [
                                'Ignore\\Me\\' => ['ccc'],
                            ],
                        ],
                        'require'  => [
                            'something/else' => '4.5.6',
                        ],
                    ],
                    [
                        'name'     => 'baz/tab',
                        'autoload' => [
                            'psr-4' => [
                                'Baz\\Tab\\' => ['ddd'],
                            ],
                        ],
                        'require'  => [
                            'roave/enforce-type-checks' => '4.5.6',
                        ],
                    ],
                ],
                'packages-dev' => [
                    [
                        'name'     => 'taz/tar',
                        'autoload' => [
                            'psr-4' => [
                                'Taz\\Tar\\' => ['eee'],
                            ],
                        ],
                        'require'  => [
                            'roave/enforce-type-checks' => '7.8.9',
                        ],
                    ],
                ],
            ]);

        $rootPackage
            ->method('getAutoload')
            ->willReturn([
                'psr-0' => ['Foo_' => 'bar'],
            ]);

        $project = Project::fromComposerInstallationContext(
            $rootPackage,
            $locker,
            __DIR__
        );

        self::assertEquals(
            PackagesRequiringStrictChecks::fromComposerLocker($locker, __DIR__),
            $project->packagesRequiringStrictTypeChecks()
        );
        self::assertEquals(
            PackageAutoload::fromAutoloadDefinition(
                [
                    'psr-0' => ['Foo_' => 'bar'],
                ],
                __DIR__
            ),
            $project->rootPackageAutoload()
        );
        self::assertFalse($project->strictTypeChecksAreEnforcedByLocalInstallation());
    }

    public function testPackageWithLocalPluginInstallation() : void
    {
        $rootPackage = $this->createMock(RootPackageInterface::class);
        $locker      = $this->createMock(Locker::class);

        $locker
            ->method('getLockData')
            ->willReturn([
                'packages' => [
                    [
                        'name'     => 'foo/bar',
                        'autoload' => [
                            'psr-4' => [
                                'Foo\\Bar\\' => ['aaa'],
                                'Foo\\Baz\\' => ['bbb'],
                            ],
                        ],
                        'require'  => [
                            'roave/enforce-type-checks' => '1.2.3',
                        ],
                    ],
                    [
                        'name' => 'roave/enforce-type-checks',
                    ],
                ],
            ]);

        $rootPackage
            ->method('getAutoload')
            ->willReturn([
                'psr-0' => ['Foo_' => 'bar'],
            ]);

        self::assertTrue(
            Project::fromComposerInstallationContext(
                $rootPackage,
                $locker,
                __DIR__
            )
                   ->strictTypeChecksAreEnforcedByLocalInstallation()
        );
    }

    public function testPackageWithLocalPluginDevInstallation() : void
    {
        $rootPackage = $this->createMock(RootPackageInterface::class);
        $locker      = $this->createMock(Locker::class);

        $locker
            ->method('getLockData')
            ->willReturn([
                'packages'     => [
                    [
                        'name'     => 'foo/bar',
                        'autoload' => [
                            'psr-4' => [
                                'Foo\\Bar\\' => ['aaa'],
                                'Foo\\Baz\\' => ['bbb'],
                            ],
                        ],
                        'require'  => [
                            'roave/enforce-type-checks' => '1.2.3',
                        ],
                    ],
                ],
                'packages-dev' => [
                    [
                        'name' => 'roave/enforce-type-checks',
                    ],
                ],
            ]);

        $rootPackage
            ->method('getAutoload')
            ->willReturn([
                'psr-0' => ['Foo_' => 'bar'],
            ]);

        self::assertTrue(
            Project::fromComposerInstallationContext(
                $rootPackage,
                $locker,
                __DIR__
            )
                   ->strictTypeChecksAreEnforcedByLocalInstallation()
        );
    }
}
