#!/usr/bin/env bash

set -euxo pipefail

CURRENT_ROAVE_DEV_VERSION=$(git rev-parse --abbrev-ref HEAD)

# The local version of the repository is not the stable/released one:
sed -i s~@CURRENT_ROAVE_DEV_VERSION~dev-$CURRENT_ROAVE_DEV_VERSION~g my-awesome-library/composer.json

# Composer cannot install a source package in itself, so we make a copy:
git checkout -- my-awesome-library/composer.json
rm -rf ./a-project/composer.lock
rm -rf ./a-project/vendor
rm -rf ./roave-you-are-using-it-wrong
cp -r ./.. /tmp/roave-you-are-using-it-wrong-example
mv /tmp/roave-you-are-using-it-wrong-example ./roave-you-are-using-it-wrong

cd a-project
set +e

# This command should fail with something like following:
# > Argument 1 of My\AwesomeLibrary\MyHelloWorld::sayhello expects
# > array<array-key, string>, array{0:int(123), 1:int(456)} provided
composer install && exit 1 || exit 0
