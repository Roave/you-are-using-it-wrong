<?php

declare(strict_types=1);

namespace RoaveTest\YouAreUsingItWrong\Composer;

use Composer\IO\IOInterface;
use Composer\Package\Locker;
use PHPUnit\Framework\TestCase;
use Roave\YouAreUsingItWrong\Composer\PackagesRequiringStrictChecks;

/**
 * @uses   \Roave\YouAreUsingItWrong\Composer\Package
 * @uses   \Roave\YouAreUsingItWrong\Composer\PackageAutoload
 *
 * @covers \Roave\YouAreUsingItWrong\Composer\PackagesRequiringStrictChecks
 */
final class PackagesRequiringStrictChecksTest extends TestCase
{
    public function testNamespacesAndPackagesToBeTypeChecked(): void
    {
        $locker = $this->createMock(Locker::class);

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
                        'require'  => ['roave/you-are-using-it-wrong' => '1.2.3'],
                    ],
                    [
                        'name'     => 'ignore/me',
                        'autoload' => [
                            'psr-4' => [
                                'Ignore\\Me\\' => ['ccc'],
                            ],
                        ],
                        'require'  => ['something/else' => '4.5.6'],
                    ],
                    [
                        'name'     => 'baz/tab',
                        'autoload' => [
                            'psr-4' => [
                                'Baz\\Tab\\' => ['ddd'],
                            ],
                        ],
                        'require'  => ['roave/you-are-using-it-wrong' => '4.5.6'],
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
                        'require'  => ['roave/you-are-using-it-wrong' => '7.8.9'],
                    ],
                ],
            ]);

        $packages = PackagesRequiringStrictChecks::fromComposerLocker($locker, __DIR__);

        self::assertSame(
            [
                'Foo\\Bar\\',
                'Foo\\Baz\\',
                'Baz\\Tab\\',
                'Taz\\Tar\\',
            ],
            $packages->namespacesForWhichUsagesAreToBeTypeChecked(),
        );
        self::assertSame(
            [
                'foo/bar',
                'baz/tab',
                'taz/tar',
            ],
            $packages->packagesForWhichUsagesAreToBeTypeChecked(),
        );

        $io = $this->createMock(IOInterface::class);

        $io
            ->expects(self::exactly(8))
            ->method('write')
            ->withConsecutive(
                ['<info>roave/you-are-using-it-wrong:</info> following package usages will be checked:'],
                [' - foo/bar'],
                [' - - Foo\\Bar\\'],
                [' - - Foo\\Baz\\'],
                [' - baz/tab'],
                [' - - Baz\\Tab\\'],
                [' - taz/tar'],
                [' - - Taz\\Tar\\'],
            );

        $packages->printPackagesToBeCheckedToComposerIo($io);
    }

    public function testCanBeBuiltFromEmptyLockData(): void
    {
        $locker = $this->createMock(Locker::class);

        $locker
            ->method('getLockData')
            ->willReturn([
                'packages' => [],
            ]);

        $packages = PackagesRequiringStrictChecks::fromComposerLocker($locker, __DIR__);

        self::assertEmpty($packages->namespacesForWhichUsagesAreToBeTypeChecked());
        self::assertEmpty($packages->packagesForWhichUsagesAreToBeTypeChecked());
    }
}
