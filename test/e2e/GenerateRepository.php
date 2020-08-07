<?php

declare(strict_types=1);

namespace RoaveE2ETest\YouAreUsingItWrong;

use Symfony\Component\Process\Process;

use function array_filter;
use function array_map;
use function array_merge;
use function copy;
use function file_put_contents;
use function glob;
use function json_encode;
use function mkdir;
use function realpath;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

use const JSON_PRETTY_PRINT;

final class GenerateRepository
{
    private function __construct()
    {
    }

    public static function generateRepository(string ...$dependencies): string
    {
        $installationTargetPath = tempnam(sys_get_temp_dir(), 'test-installation-');

        unlink($installationTargetPath);
        mkdir($installationTargetPath);
        mkdir($installationTargetPath . '/src');

        $currentGitVersion = (new Process(['git', 'rev-parse', '--abbrev-ref', 'HEAD'], __DIR__ . '/../..'))
            ->mustRun()
            ->getOutput();

        $vendorDependencies = array_filter(
            array_map(
                static function (string $path): string {
                    return (string) realpath($path);
                },
                glob(__DIR__ . '/../../vendor/*/*')
            ),
            'is_dir'
        );

        self::addVersionToDependencies($vendorDependencies);

        file_put_contents(
            $installationTargetPath . '/composer.json',
            json_encode(
                [
                    'minimum-stability' => 'dev',
                    'autoload'          => [
                        'psr-4' => ['Project\\' => './src'],
                    ],
                    'require'           => array_merge(
                        ['roave/you-are-using-it-wrong' => 'dev-' . $currentGitVersion],
                        ...array_map(static function (string $dependency) use ($currentGitVersion): array {
                            return [$dependency => 'dev-' . $currentGitVersion];
                        }, $dependencies)
                    ),
                    'repositories'      => array_merge(
                        // @NOTE: disabling packagist won't work because this repository has complex dependencies
                        // that need to be looked up at very specific versions
                        // [['packagist.org' => false]],
                        [],
                        array_map(
                            static function (string $path): array {
                                return [
                                    'type' => 'path',
                                    'url'  => $path,
                                ];
                            },
                            array_merge(
                                $vendorDependencies,
                                [
                                    (string) realpath(__DIR__ . '/../..'),
                                    (string) realpath(__DIR__ . '/../repositories/empty-repository'),
                                    (string) realpath(__DIR__ . '/../repositories/project-with-violations'),
                                    (string) realpath(__DIR__ . '/../repositories/repository-depending-on-type-checks'),
                                    (string) realpath(__DIR__ . '/../repositories/repository-indirectly-depending-on-type-checks'),
                                    (string) realpath(__DIR__ . '/../repositories/repository-not-depending-on-type-checks'),
                                ]
                            )
                        )
                    ),
                ],
                JSON_PRETTY_PRINT
            )
        );

        copy(
            __DIR__ . '/../repositories/project-with-violations/src/file-with-violations.php',
            $installationTargetPath . '/src/file-with-violations.php'
        );

        return $installationTargetPath;
    }

    private static function addVersionToDependencies(array $vendorDependencies): void
    {
        $composerLockPath = __DIR__ . '/../../composer.lock';

        $composerLockPackages = json_decode(file_get_contents($composerLockPath))->packages;

        foreach ($vendorDependencies as $dependencyPath) {
            $composerJson = json_decode(file_get_contents($dependencyPath . '/composer.json'));

            $packageName = $composerJson->name;

            $packageLockData = array_values(array_filter(
                $composerLockPackages,
                static function (object $package) use ($packageName) {
                    return $package->name === $packageName;
                }
            ));

            if (isset($packageLockData[0])) {
                $packageLockData = $packageLockData[0];
            }

            $packageLockVersion = $packageLockData->version;

            $composerJson->version = $packageLockVersion;

            file_put_contents($dependencyPath . '/composer.json', json_encode($composerJson));
        }
    }
}
