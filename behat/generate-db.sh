#!/bin/sh

SCRIPT_DIR=$(readlink -f ${0%/*})

cd $SCRIPT_DIR

# Export the databases
php ../bin/inventory db:populate
mysqldump -u root --password='' --single-transaction --opt inventory > fixtures/emptyOneUser.sql
php ../bin/inventory db:populate test
mysqldump -u root --password='' --single-transaction --opt inventory > fixtures/full.sql
php ../bin/inventory db:populate testDW
mysqldump -u root --password='' --single-transaction --opt inventory > fixtures/forTestDWUpToDate.sql
