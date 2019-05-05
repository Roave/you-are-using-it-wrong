<?php

declare(strict_types=1);

namespace Roave\YouAreUsingItWrong\Psalm;

use Psalm\Config\ProjectFileFilter;
use Roave\YouAreUsingItWrong\Composer\PackageAutoload;

/** @internal this class is only for configuring psalm according to the defaults of this repository */
final class ProjectFilesToBeTypeChecked extends ProjectFileFilter
{
    /** {@inheritDoc} */
    public function __construct(bool $inclusive)
    {
        parent::__construct($inclusive);
    }

    public static function fromAutoloadDefinitions(PackageAutoload $autoload) : self
    {
        $instance = new self(true);

        foreach ($autoload->directories() as $directory) {
            $instance->addDirectory($directory);
        }

        foreach ($autoload->files() as $file) {
            $instance->addFile($file);
        }

        return $instance;
    }
}
