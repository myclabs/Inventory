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
Xvfb :99 -ac > /dev/null 2>&1 &
export DISPLAY=:99

# Start selenium server
java -jar selenium-server-standalone.jar > /dev/null 2>&1 &

sleep 2

# Zombie.js
#export NODE_PATH=/usr/local/lib/node_modules

# Behat
php ../vendor/behat/behat/bin/behat --config behat.yml --name "Identification"
