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

sleep 7

# Clear Memcached
echo 'flush_all' | netcat localhost 11211

# Composer
cd ..
composer install --optimize-autoloader
cd behat

# Export the databases
./generate-db.sh

# Zombie.js
#export NODE_PATH=/usr/local/lib/node_modules

# Behat
php ../vendor/behat/behat/bin/behat --config behat.yml --format pretty,failed --rerun failed.txt && rm -f failed.txt
