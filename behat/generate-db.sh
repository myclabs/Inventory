#!/bin/sh

SCRIPT_DIR=$(readlink -f ${0%/*})

cd $SCRIPT_DIR

# Export the databases
php ../scripts/build/build.php create update
php ../scripts/build/build.php populate
mysqldump -u root --password='' --single-transaction --opt inventory > fixtures/emptyOneUser.sql
php ../scripts/build/build.php populateTest
mysqldump -u root --password='' --single-transaction --opt inventory > fixtures/full.sql
php ../scripts/build/build.php create update
php ../scripts/build/build.php populate populateTestDWUpToDate
mysqldump -u root --password='' --single-transaction --opt inventory > fixtures/forTestDWUpToDate.sql
