#!/bin/sh

trap 'killall' INT

killall() {
    trap '' INT TERM     # ignore INT and TERM while shutting down
    echo "**** Shutting down... ****"     # added double quotes
    kill -TERM 0         # fixed order, send TERM not INT
    wait
    echo DONE
}

# Git pull
git pull

# Start virtual display
#Xvfb :99 -ac > /dev/null 2>&1 &
#export DISPLAY=:99

export DISPLAY=:0

# Start selenium server
java -jar selenium-server-standalone.jar > selenium.log 2>&1 &

sleep 5

# Zombie.js
#export NODE_PATH=/usr/local/lib/node_modules

# Behat
php ../vendor/behat/behat/bin/behat --config behat.yml --rerun failed.txt && rm failed.txt
