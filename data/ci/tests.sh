#!/bin/sh
# Run tests for continuous integration

# Lance dans des process séparés sinon problèmes
php scripts/build/build.php -e testsunitaires create update
php scripts/build/build.php -e testsunitaires populate

# Start worker
php scripts/jobs/work/work.php > /dev/null &

phpunit -c phpunit-ci.xml

# Kill worker
kill $!

php scripts/build/build.php -e testsunitaires create update

# Tests de unit
phpunit --bootstrap tests/init.php tests/Unit
