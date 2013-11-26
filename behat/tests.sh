#!/bin/sh

trap 'killall' INT

killall() {
    trap '' INT TERM     # ignore INT and TERM while shutting down
    echo "**** Shutting down... ****"     # added double quotes
    kill -TERM 0         # fixed order, send TERM not INT
    wait
    echo DONE
}

# Start virtual display
#Xvfb :99 -ac > /dev/null 2>&1 &
#export DISPLAY=:99

export DISPLAY=:0

# Start selenium server
java -jar selenium-server-standalone.jar > selenium.log 2>&1 &

sleep 8

# Export the databases
php ../scripts/build/build.php create update populate
mysqldump -u root --password='' --single-transaction --opt inventory > fixtures/emptyOneUser.sql
php ../scripts/build/build.php populateTest
mysqldump -u root --password='' --single-transaction --opt inventory > fixtures/full.sql
php ../scripts/build/build.php create update populate populateTestDWUpToDate
mysqldump -u root --password='' --single-transaction --opt inventory > fixtures/forTestDWUpToDate.sql

# Zombie.js
#export NODE_PATH=/usr/local/lib/node_modules

# Behat
php ../vendor/behat/behat/bin/behat --config behat.yml --rerun failed.txt && rm -f failed.txt
