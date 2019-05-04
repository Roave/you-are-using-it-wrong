# roave/you-are-using-it-wrong

[![Build Status](https://travis-ci.org/roave/you-are-using-it-wrong.svg?branch=master)](https://travis-ci.org/roave/you-are-using-it-wrong)
[![Packagist](https://img.shields.io/packagist/v/roave/you-are-using-it-wrong.svg)](https://packagist.org/packages/roave/you-are-using-it-wrong)

This package enforces type checks during composer installation in downstream
consumers of your package.

`roave/you-are-using-it-wrong` comes with a zero-configuration out-of-the-box
setup.

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
composer require roave/you-are-doing-it-wrong
```

No further changes are needed for this tool to start operating as per its
design, if your declared types are already reflecting your library requirements.

Please also note that this should **not** be used in `"require-dev"`, but
specifically in `"require"` in order for the type checks to be applied to
downstream consumers of your code.

### Examples

Assuming you have a library with following `composer.json`:

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
        "roave/you-are-doing-it-wrong": "@STABLE"
    }
}
```

Given following `src/MyHelloWorld.php`:

```php
<?php

namespace My\AwesomeLibrary;

final class MyHelloWorld
{
    public static function sayHello(string $name) : string
    {
        return 'Hello ' . $name . '!';
    }
}
```

Considering following downstream project:

```json
{
    "name": "a/project",
    "autoload": {
        "psr-4": {
            "The\\Project\\": "src"
        }
    }
}
```

And following `src/MyExample.php`:

```php
<?php

// notice the simple type error
echo \My\AwesomeLibrary\MyHelloWorld::sayHello(123);
```

Then following command will fail with a type check error and description:

```sh
composer require my/awesome-library:^1.0.0
```

## Workarounds

This package is designed to be quite invasive from a type-check perspective,
but it will bail out of any checks if a [`psalm.xml`](https://psalm.dev/docs/configuration/)
is detected in the root of your project. If that is the case, the tool assumes
that the author of the project is already responsible for ensuring type-safety
within their own domain, and therefore bails out without performing further
checks.

As mentioned above, the design of the tool circles around raising awareness of
static type usage in the PHP ecosystem, and therefore it will only give up if
it is sure that you are already taking care of the matter on your own.

## Professional Support

If you need help with setting up this library in your project, you can contact
us at team@roave.com for consulting/support.
