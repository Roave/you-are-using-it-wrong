<?php

declare(strict_types=1);

namespace RoaveTest\YouAreUsingItWrong\Composer;

use Composer\Package\RootPackageInterface;
use PHPUnit\Framework\TestCase;
use Roave\YouAreUsingItWrong\Composer\PackageAutoload;

use function realpath;

/** @covers \Roave\YouAreUsingItWrong\Composer\PackageAutoload */
final class PackageAutoloadTest extends TestCase
{
    /**
     * @param array<string, mixed> $autoloadDefinition
     * @param array<int, string>   $expectedDirectories
     *
     * @dataProvider expectedDirectories
     */
    public function testDirectoriesFromAutoloadDefinition(
        array $autoloadDefinition,
        array $expectedDirectories,
    ): void {
        self::assertSame(
            $expectedDirectories,
            PackageAutoload::fromAutoloadDefinition($autoloadDefinition, realpath(__DIR__ . '/..'))
                           ->directories(),
        );
    }

    /**
     * @param array<string, mixed> $autoloadDefinition
     * @param array<int, string>   $expectedDirectories
     *
     * @dataProvider expectedDirectories
     */
    public function testDirectoriesFromComposerRootPackage(
        array $autoloadDefinition,
        array $expectedDirectories,
    ): void {
        $rootPackage = $this->createMock(RootPackageInterface::class);

        $rootPackage
            ->method('getAutoload')
            ->willReturn($autoloadDefinition);

        self::assertSame(
            $expectedDirectories,
            PackageAutoload::fromComposerRootPackage($rootPackage, realpath(__DIR__ . '/..'))
                           ->directories(),
        );
    }

    /**
     * @return array<string, mixed>
     * @psalm-return array<string, array{0: array{
     *  psr-0?: array<string, string|array<int, string>>,
     *  psr-4?: array<string, string|array<int, string>>
     * }, 1: array<int, string>}>
     */
    public function expectedDirectories(): array
    {
        return [
            'empty definition'                                    => [
                [],
                [],
            ],
            'definition with non-existing psr-4 single directory' => [
                [
                    'psr-4' => ['Non\\Existing\\' => 'foo'],
                ],
                [],
            ],
            'definition with non-existing psr-4 array directory'  => [
                [
                    'psr-4' => ['Non\\Existing\\' => ['foo']],
                ],
                [],
            ],
            'definition with existing psr-4 single directory'     => [
                [
                    'psr-4' => ['Non\\Existing\\' => 'Composer'],
                ],
                [
                    realpath(__DIR__),
                ],
            ],
            'definition with multiple existing psr-4 directories' => [
                [
                    'psr-4' => [
                        'Non\\Existing\\' => 'Composer',
                        'Another\\Ns\\'   => 'Psalm',
                    ],
                ],
                [
                    realpath(__DIR__),
                    realpath(__DIR__ . '/../Psalm'),
                ],
            ],
            'definition with non-existing psr-0 single directory' => [
                [
                    'psr-0' => ['Non_Existing_' => 'foo'],
                ],
                [],
            ],
            'definition with non-existing psr-0 array directory'  => [
                [
                    'psr-0' => ['Non_Existing_' => ['foo']],
                ],
                [],
            ],
            'definition with existing psr-0 single directory'     => [
                [
                    'psr-0' => ['Non_Existing_' => 'Composer'],
                ],
                [
                    realpath(__DIR__),
                ],
            ],
            'definition with multiple existing psr-0 directories' => [
                [
                    'psr-0' => [
                        'Non_Existing_' => 'Composer',
                        'Another_Ns_'   => 'Psalm',
                    ],
                ],
                [
                    realpath(__DIR__),
                    realpath(__DIR__ . '/../Psalm'),
                ],
            ],
            'definition with non-existing classmap'               => [
                [
                    'classmap' => ['non-existing'],
                ],
                [],
            ],
            'definition with classmap pointing to file'           => [
                [
                    'classmap' => ['Composer/PackageAutoloadTest.php'],
                ],
                [],
            ],
            'definition with directory classmap'                  => [
                [
                    'classmap' => ['Composer', 'Psalm'],
                ],
                [
                    realpath(__DIR__),
                    realpath(__DIR__ . '/../Psalm'),
                ],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $autoloadDefinition
     * @param array<int, string>   $expectedFiles
     *
     * @dataProvider expectedFiles
     */
    public function testFilesFromAutoloadDefinition(
        array $autoloadDefinition,
        array $expectedFiles,
    ): void {
        self::assertSame(
            $expectedFiles,
            PackageAutoload::fromAutoloadDefinition($autoloadDefinition, realpath(__DIR__ . '/..'))
                           ->files(),
        );
    }

    /**
     * @param array<string, mixed> $autoloadDefinition
     * @param array<int, string>   $expectedFiles
     *
     * @dataProvider expectedFiles
     */
    public function testFilesFromComposerRootPackage(
        array $autoloadDefinition,
        array $expectedFiles,
    ): void {
        $rootPackage = $this->createMock(RootPackageInterface::class);

        $rootPackage
            ->method('getAutoload')
            ->willReturn($autoloadDefinition);

        self::assertSame(
            $expectedFiles,
            PackageAutoload::fromComposerRootPackage($rootPackage, realpath(__DIR__ . '/..'))
                           ->files(),
        );
    }

    /**
     * @return array<string, mixed>
     * @psalm-return array<string, array{0: array{
     *   classmap?: array<int, string>,
     *   files?: array<int, string>
     * }, 1: array<int, string>}>
     */
    public function expectedFiles(): array
    {
        return [
            'empty definition'                          => [
                [],
                [],
            ],
            'definition with non-existing classmap'     => [
                [
                    'classmap' => ['non-existing'],
                ],
                [],
            ],
            'definition with classmap pointing to file' => [
                [
                    'classmap' => ['Composer/PackageAutoloadTest.php'],
                ],
                [
                    realpath(__FILE__),
                ],
            ],
            'definition with file pointing to non-existing file' => [
                [
                    'files' => ['non-existing'],
                ],
                [],
            ],
            'definition with file pointing to existing file' => [
                [
                    'files' => ['Composer/PackageAutoloadTest.php'],
                ],
                [
                    realpath(__FILE__),
                ],
            ],
            'definition with directory classmap'        => [
                [
                    'classmap' => ['Composer', 'Psalm'],
                ],
                [],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $autoloadDefinition
     * @param array<int, string>   $expectedNamespaces
     *
     * @dataProvider expectedNamespaces
     */
    public function testNamespacesFromAutoloadDefinition(
        array $autoloadDefinition,
        array $expectedNamespaces,
    ): void {
        self::assertSame(
            $expectedNamespaces,
            PackageAutoload::fromAutoloadDefinition($autoloadDefinition, realpath(__DIR__ . '/..'))
                           ->namespaces(),
        );
    }

    /**
     * @param array<string, mixed> $autoloadDefinition
     * @param array<int, string>   $expectedNamespaces
     *
     * @dataProvider expectedNamespaces
     */
    public function testNamespacesFromRootPackage(
        array $autoloadDefinition,
        array $expectedNamespaces,
    ): void {
        $rootPackage = $this->createMock(RootPackageInterface::class);

        $rootPackage
            ->method('getAutoload')
            ->willReturn($autoloadDefinition);

        self::assertSame(
            $expectedNamespaces,
            PackageAutoload::fromComposerRootPackage($rootPackage, realpath(__DIR__ . '/..'))
                           ->namespaces(),
        );
    }

    /**
     * @return array<string, mixed>
     * @psalm-return array<string, array{0: array{
     *  psr-0?: array<string, string|array<int, string>>,
     *  psr-4?: array<string, string|array<int, string>>
     * }, 1: array<int, string>}>
     */
    public function expectedNamespaces(): array
    {
        return [
            'empty definition'                                    => [
                [],
                [],
            ],
            'definition with non-existing psr-4 single directory' => [
                [
                    'psr-4' => ['Non\\Existing\\' => 'foo'],
                ],
                ['Non\\Existing\\'],
            ],
            'definition with non-existing psr-4 array directory'  => [
                [
                    'psr-4' => ['Non\\Existing\\' => ['foo']],
                ],
                ['Non\\Existing\\'],
            ],
            'definition with existing psr-4 single directory'     => [
                [
                    'psr-4' => ['Non\\Existing\\' => 'Composer'],
                ],
                ['Non\\Existing\\'],
            ],
            'definition with multiple existing psr-4 directories' => [
                [
                    'psr-4' => [
                        'Non\\Existing\\' => 'Composer',
                        'Another\\Ns\\'   => 'Psalm',
                    ],
                ],
                [
                    'Non\\Existing\\',
                    'Another\\Ns\\',
                ],
            ],
            'definition with non-existing psr-0 single directory' => [
                [
                    'psr-0' => ['Non_Existing_' => 'foo'],
                ],
                ['Non_Existing_'],
            ],
            'definition with non-existing psr-0 array directory'  => [
                [
                    'psr-0' => ['Non_Existing_' => ['foo']],
                ],
                ['Non_Existing_'],
            ],
            'definition with existing psr-0 single directory'     => [
                [
                    'psr-0' => ['Non_Existing_' => 'Composer'],
                ],
                ['Non_Existing_'],
            ],
            'definition with multiple existing psr-0 directories' => [
                [
                    'psr-0' => [
                        'Non_Existing_' => 'Composer',
                        'Another_Ns_'   => 'Psalm',
                    ],
                ],
                [
                    'Non_Existing_',
                    'Another_Ns_',
                ],
            ],
        ];
    }
}
