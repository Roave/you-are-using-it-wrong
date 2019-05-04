<?php

declare(strict_types=1);

namespace Roave\YouAreUsingItWrong\Composer;

use Composer\Package\Locker;

/** @internal this class is only for supporting internal usage of locker data */
final class PackagesRequiringStrictChecks
{
    /** @var Package[] */
    private $packages;

    private function __construct(Package ...$packages)
    {
        $this->packages = $packages;
    }

    public static function fromComposerLocker(Locker $locker, string $projectInstallationPath) : self
    {
        /**
         * @var array{
         *  packages: array<int, array{
         *   name: string,
         *   require: null|array<string, string>,
         *   autoload: null|array{
         *    psr-4: null|array<string, string|array<int, string>>,
         *    psr-0: null|array<string, string|array<int, string>>
         *   }
         *  }>,
         *  packages-dev: null|array<int, array{
         *   name: string,
         *   require: null|array<string, string>,
         *   autoload: null|array{
         *    psr-4: null|array<string, string|array<int, string>>,
         *    psr-0: null|array<string, string|array<int, string>>
         *   }
         *  }>
         * } $lockData
         */
        $lockData = $locker->getLockData();

        return new self(...array_filter(
            array_map(
                function (array $packageDefinition) use ($projectInstallationPath) : Package {
                    return Package::fromPackageDefinition(
                        $packageDefinition,
                        $projectInstallationPath . '/vendor/' . $packageDefinition['name']
                    );
                },
                array_merge($lockData['packages'], $lockData['packages-dev'] ?? [])
            ),
            static function (Package $package) : bool {
                return $package->requiresStrictChecks();
            }
        ));
    }

    /** @return array<int, string> */
    public function packagesForWhichUsagesAreToBeTypeChecked() : array
    {
        return array_map(static function (Package $package) : string {
            return $package->name();
        }, $this->packages);
    }

    /** @return array<int, string> */
    public function namespacesForWhichUsagesAreToBeTypeChecked() : array
    {
        return array_merge([], ...array_map(static function (Package $package) : array {
            return $package
                ->autoload()
                ->namespaces();
        }, $this->packages));
    }
}
