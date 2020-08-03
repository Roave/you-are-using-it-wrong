# roave/you-are-using-it-wrong

[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Froave%2Fyou-are-using-it-wrong)](https://dashboard.stryker-mutator.io/reports/github.com/roave/you-are-using-it-wrong/master)
[![Type Coverage](https://shepherd.dev/github/roave/you-are-using-it-wrong/coverage.svg)](https://shepherd.dev/github/roave/you-are-using-it-wrong)
[![Packagist](https://img.shields.io/packagist/v/roave/you-are-using-it-wrong.svg)](https://packagist.org/packages/roave/you-are-using-it-wrong)

This package enforces type checks during composer installation in downstream
consumers of your package. This only applies to usages of classes, properties,
methods and functions declared within packages that directly depend on
*roave/you-are-using-it-wrong*.

Issues that the static analyser finds that do not relate to these namespaces
will not be reported.

`roave/you-are-using-it-wrong` comes with a zero-configuration out-of-the-box
setup.

By default, it hooks into `composer install` and `composer update`, preventing
a successful command execution if there are type errors in usages of protected
namespaces. 

The usage of this plugin is highly endorsed for authors of new PHP libraries
who appreciate the advantages of static types.

This project is built with the hope that libraries with larger user-bases will
raise awareness of type safety (or current lack thereof) in the PHP ecosystem.

As annoying as it might sound, it is not uncommon for library maintainers to
respond to support questions caused by lack of type checks in downstream
projects. In addition to that, relying more on static types over runtime checks,
it is possible to reduce code size and maintenance burden by strengthening the
API boundaries of a library.

### Installation

This package is designed to be installed as a dependency of PHP **libraries**.

In your library, add it to your
`composer.json`:

```sh
composer require roave/you-are-using-it-wrong
```

No further changes are needed for this tool to start operating as per its
design, if your declared types are already reflecting your library requirements.

Please also note that this should **not** be used in `"require-dev"`, but
specifically in `"require"` in order for the type checks to be applied to
downstream consumers of your code.

### Examples

You can experiment with the following example by running `cd examples && ./run-example.sh`.

Given you are the author of `my/awesome-library`, which has following `composer.json`:

```json
{
    "name": "my/awesome-library",
    "type": "library",
    "autoload": {
        "psr-4": {
            "My\\AwesomeLibrary\\": "src"
        }
    },
    "require": {
        "roave/you-are-using-it-wrong": "^1.0.0"
    }
}
```

Given following `my/awesome-library/src/MyHelloWorld.php`:

```php
<?php declare(strict_types=1);

namespace My\AwesomeLibrary;

final class MyHelloWorld
{
    /** @param array<string> $people */
    public static function sayHello(array $people) : string
    {
        return 'Hello ' . implode(', ', $people) . '!';
    }
}
```

Given following downstream `a/project/composer.json` project that
depends on your `my/awesome-library`:

```json
{
    "name": "a/project",
    "type": "project",
    "autoload": {
        "psr-4": {
            "The\\Project\\": "src"
        }
    },
    "require": {
        "my/awesome-library": "^1.0.0"
    }
}
```

And following `a/project/src/MyExample.php`:

```php
<?php declare(strict_types=1);

// notice the simple type error
echo \My\AwesomeLibrary\MyHelloWorld::sayHello([123, 456]);
```

Then `composer install` in said project will fail:

```sh
$ cd a/project
$ composer install

Loading composer repositories with package information
Updating dependencies (including require-dev)
  ... <snip>
  - Installing roave/you-are-using-it-wrong (1.0.0): ...
  - Installing my/awesome-library (1.0.0): ...
  ... <snip>

roave/you-are-using-it-wrong: checking strictly type-checked packages...
Scanning files...
Analyzing files...

ERROR: InvalidScalarArgument - a-project/src/MyExample.php:4:48 
  - Argument 1 of My\AwesomeLibrary\MyHelloWorld::sayhello expects array<array-key, string>,
    array{0:int(123), 1:int(456)} provided
echo \My\AwesomeLibrary\MyHelloWorld::sayHello([123, 456]);

$ echo $?
1
```

## Workarounds

This package is designed to be quite invasive from a type-check perspective,
but it will bail out of any checks if a [`psalm configuration`](https://psalm.dev/docs/configuration/)
is detected in the root of the installation/project.
If that is the case, the tool assumes that the author of the project is already
responsible for ensuring type-safety within their own domain, and therefore
bails out without performing further checks.

As mentioned above, the design of the tool circles around raising awareness of
static type usage in the PHP ecosystem, and therefore it will only give up if
it is sure that library consumers are already taking care of the matter on their
own.

## Professional Support

If you need help with setting up this library in your project, you can contact
us at team@roave.com for consulting/support.
