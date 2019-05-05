<?php

declare(strict_types=1);

namespace Roave\YouAreUsingItWrong\Composer;

use Composer\IO\IOInterface;
use Composer\Package\Locker;
use function array_filter;
use function array_map;
use function array_merge;
use function array_walk;

/** @internal this class is only for supporting internal usage of locker data */
final class PackagesRequiringStrictChecks
{
    private const THIS_PACKAGE_NAME = 'roave/you-are-using-it-wrong';

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
         *   require?: array<string, string>,
         *   autoload?: array{
         *    psr-4?: array<string, string|array<int, string>>,
         *    psr-0?: array<string, string|array<int, string>>
         *   }
         *  }>,
         *  packages-dev?: array<int, array{
         *   name: string,
         *   require?: array<string, string>,
         *   autoload?: array{
         *    psr-4?: array<string, string|array<int, string>>,
         *    psr-0?: array<string, string|array<int, string>>
         *   }
         *  }>
         * } $lockData
         */
        $lockData = $locker->getLockData();

        return new self(...array_filter(
            array_map(
                static function (array $packageDefinition) use ($projectInstallationPath) : Package {
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

    public function printPackagesToBeCheckedToComposerIo(IOInterface $io) : void
    {
        $io->write('<info>' . self::THIS_PACKAGE_NAME . ':</info> following package usages will be checked:');

        array_walk(
            $this->packages,
            static function (Package $package) use ($io) : void {
                self::printPackage($package, $io);
            }
        );
    }

    private static function printPackage(Package $package, IOInterface $io) : void
    {
        $io->write(' - ' . $package->name());

        $namespaces = $package->autoload()->namespaces();

        array_walk(
            $namespaces,
            static function (string $namespace) use ($io) : void {
                self::printPackageNamespace($namespace, $io);
            }
        );
    }

    private static function printPackageNamespace(string $namespace, IOInterface $io) : void
    {
        $io->write(' - - ' . $namespace);
    }
}
