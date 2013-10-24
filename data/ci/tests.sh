#!/bin/sh
# Run tests for continuous integration

set -e

# Lance dans des process séparés sinon problèmes
php scripts/build/build.php -e testsunitaires create update
php scripts/build/build.php -e testsunitaires populate

phpunit -c phpunit.xml

php scripts/build/build.php -e testsunitaires create update

# Tests de unit
phpunit --bootstrap tests/init.php tests/Unit
