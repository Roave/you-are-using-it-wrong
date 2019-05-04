<?php

declare(strict_types=1);

namespace Roave\YouAreUsingItWrong\Composer;

use Composer\Package\Locker;
use Composer\Package\RootPackageInterface;
use function array_filter;
use function array_merge;

/** @internal this class is only for supporting internal usage of locker data */
final class Project
{
    private const THIS_PACKAGE_NAME = 'roave/enforce-type-checks';

    /** @var PackageAutoload */
    private $rootPackageAutoload;

    /** @var PackagesRequiringStrictChecks */
    private $packagesRequiringStrictTypeChecks;

    /** @var bool */
    private $strictTypeChecksAreEnforcedByLocalInstallation;

    private function __construct(
        PackageAutoload $rootPackageAutoload,
        PackagesRequiringStrictChecks $packagesRequiringStrictChecks,
        bool $strictTypeChecksAreEnforcedByLocalInstallation
    ) {
        $this->rootPackageAutoload                            = $rootPackageAutoload;
        $this->packagesRequiringStrictTypeChecks              = $packagesRequiringStrictChecks;
        $this->strictTypeChecksAreEnforcedByLocalInstallation = $strictTypeChecksAreEnforcedByLocalInstallation;
    }

    public static function fromComposerInstallationContext(
        RootPackageInterface $rootPackage,
        Locker $locker,
        string $currentWorkingDirectory
    ) : self {
        /** @psalm-var array{packages: array<int, array{name: string}>, packages-dev?: array<int, array{name: string}>} $lockData */
        $lockData = $locker->getLockData();

        return new self(
            PackageAutoload::fromComposerRootPackage($rootPackage, $currentWorkingDirectory),
            PackagesRequiringStrictChecks::fromComposerLocker($locker, $currentWorkingDirectory),
            array_filter(
                array_merge($lockData['packages'], $lockData['packages-dev'] ?? []),
                static function (array $package) : bool {
                    return $package['name'] === self::THIS_PACKAGE_NAME;
                }
            ) !== []
        );
    }

    public function rootPackageAutoload() : PackageAutoload
    {
        return $this->rootPackageAutoload;
    }

    public function packagesRequiringStrictTypeChecks() : PackagesRequiringStrictChecks
    {
        return $this->packagesRequiringStrictTypeChecks;
    }

    public function strictTypeChecksAreEnforcedByLocalInstallation() : bool
    {
        return $this->strictTypeChecksAreEnforcedByLocalInstallation;
    }
}
