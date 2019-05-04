<?php

declare(strict_types=1);

namespace Roave\YouAreUsingItWrong\Composer;

use Composer\Package\RootPackageInterface;

/** @internal this class is only for supporting internal usage of composer json data */
final class PackageAutoload
{
    /** @var array */
    private $psr4;

    /** @var array */
    private $psr0;

    /** @var array */
    private $classMap;

    /** @var array */
    private $files;

    /**
     * @param array<string, array<int, string>> $psr4
     * @param array<string, array<int, string>> $psr0
     * @param array<int, string>                $classMap
     * @param array<int, string>                $files
     */
    private function __construct(
        array $psr4,
        array $psr0,
        array $classMap,
        array $files
    ) {
        $this->psr4     = $psr4;
        $this->psr0     = $psr0;
        $this->classMap = $classMap;
        $this->files    = $files;
    }

    /**
     * @param mixed[] $autoloadDefinition
     *
     * @psalm-param array{
     *   psr-4: null|array<string, string|array<int, string>>,
     *   psr-0: null|array<string, string|array<int, string>>,
     *   files: null|array<int, string>,
     *   classmap: null|array<int, string>
     * } $autoloadDefinition
     */
    public static function fromAutoloadDefinition(array $autoloadDefinition, string $packageDirectory) : self
    {
        $prefixWithCurrentDir = static function (string $path) use ($packageDirectory) : string {
            return $packageDirectory . '/' . $path;
        };

        return new self(
            array_map(static function ($paths) use ($prefixWithCurrentDir) : array {
                return array_map($prefixWithCurrentDir, array_map('strval', (array) $paths));
            }, $autoloadDefinition['psr-4'] ?? []),
            array_map(static function ($paths) use ($prefixWithCurrentDir) : array {
                return array_map($prefixWithCurrentDir, array_map('strval', (array) $paths));
            }, $autoloadDefinition['psr-0'] ?? []),
            array_map($prefixWithCurrentDir, $autoloadDefinition['classmap'] ?? []),
            array_map($prefixWithCurrentDir, $autoloadDefinition['files'] ?? [])
        );
    }

    public static function fromComposerRootPackage(RootPackageInterface $package, string $projectDirectory) : self
    {
        /**
         * @psalm-var array{
         *   psr-4: null|array<string, string|array<int, string>>,
         *   psr-0: null|array<string, string|array<int, string>>,
         *   classmap: null|array<int, string>,
         *   files: null|array<int, string>
         * } $autoload
         */
        $autoload = $package->getAutoload();

        return self::fromAutoloadDefinition($autoload, $projectDirectory);
    }

    /** @return array<int, string> */
    public function directories() : array
    {
        return array_filter(array_map('realpath', array_merge(
            [],
            array_filter($this->classMap, 'is_dir'),
            ...array_values($this->psr0),
            ...array_values($this->psr4)
        )));
    }

    /** @return array<int, string> */
    public function files() : array
    {
        return array_filter(array_map('realpath', array_merge(
            [],
            array_filter($this->classMap, 'is_file'),
            $this->files
        )));
    }

    public function namespaces() : array
    {
        return array_merge(array_keys($this->psr4), array_keys($this->psr0));
    }
}
