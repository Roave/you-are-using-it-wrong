<?php

declare(strict_types=1);

namespace Roave\YouAreUsingItWrong\Psalm;

use Psalm\Config\ProjectFileFilter;
use Roave\YouAreUsingItWrong\Composer\PackageAutoload;

/** @internal this class is only for configuring psalm according to the defaults of this repository */
final class ProjectFilesToBeTypeChecked
{
    public static function fromAutoloadDefinitions(PackageAutoload $autoload): ProjectFileFilter
    {
        $instance = ProjectFileFilter::loadFromArray([], __DIR__, true);

        foreach ($autoload->directories() as $directory) {
            $instance->addDirectory($directory);
        }

        foreach ($autoload->files() as $file) {
            $instance->addFile($file);
        }

        return $instance;
    }
}
