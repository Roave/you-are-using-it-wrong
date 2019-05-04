<?php

declare(strict_types=1);

namespace RoaveE2ETest\YouAreUsingItWrong;

use Symfony\Component\Process\Process;

final class GenerateRepository
{
    private function __construct()
    {
    }

    public static function generateRepository(string ...$dependencies) : string
    {
        $installationTargetPath = tempnam(sys_get_temp_dir(), 'test-installation-');

        unlink($installationTargetPath);
        mkdir($installationTargetPath);
        mkdir($installationTargetPath . '/src');

        $currentGitVersion = (new Process(['git', 'rev-parse', '--abbrev-ref', 'HEAD'], __DIR__ . '/../..'))
            ->mustRun()
            ->getOutput();

        file_put_contents(
            $installationTargetPath . '/composer.json',
            json_encode(
                [
                    'minimum-stability' => 'dev',
                    'autoload'          => [
                        'psr-4' => ['Project\\' => './src'],
                    ],
                    'require'           => array_merge(
                        ['roave/enforce-type-checks' => 'dev-' . $currentGitVersion],
                        ...array_map(function (string $dependency) use ($currentGitVersion) : array {
                            return [$dependency => 'dev-' . $currentGitVersion];
                        }, $dependencies)
                    ),
                    'repositories'      => array_merge(
                        [
                            // @NOTE: disabling packagist won't work because this repository has complex dependencies
                            // that need to be looked up at very specific versions
                            // ['packagist.org' => false],
                        ],
                        array_map(
                            function (string $path) : array {
                                return [
                                    'type' => 'path',
                                    'url'  => $path,
                                ];
                            },
                            array_merge(
                                array_filter(
                                    array_map('realpath', glob(__DIR__ . '/../../vendor/*/*')),
                                    'is_dir'
                                ),
                                [
                                    realpath(__DIR__ . '/../..'),
                                    realpath(__DIR__ . '/../repositories/empty-repository'),
                                    realpath(__DIR__ . '/../repositories/project-with-violations'),
                                    realpath(__DIR__ . '/../repositories/repository-depending-on-type-checks'),
                                    realpath(__DIR__ . '/../repositories/repository-indirectly-depending-on-type-checks'),
                                    realpath(__DIR__ . '/../repositories/repository-not-depending-on-type-checks'),
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
}
