#!/bin/bash

# Configuration pour les VM d'intégration continue

BASEDIR=/vagrant/data/vagrant

apt-get update

# For PHP 5.4
apt-get install -y python-software-properties
add-apt-repository -y ppa:ondrej/php5
apt-get update

apt-get install -y curl git

# Mysql
export DEBIAN_FRONTEND=noninteractive
apt-get install -q -y mysql-server
mysql -u root -e "CREATE USER 'myc-sense'@'localhost' IDENTIFIED BY '';"
mysql -u root -e "GRANT ALL PRIVILEGES ON *.* TO 'myc-sense'@'localhost';"
mysql -u root -e "FLUSH PRIVILEGES;"

# PHP
apt-get install -y php5 php5-curl php5-cli php5-dev php5-mysql php-pear php5-xdebug

# Gearman
apt-get install -y build-essential
apt-get install -y gearman libgearman6 libgearman-dev gearman-tools gearman-job-server
cd /tmp
wget http://pecl.php.net/get/gearman-1.0.3.tgz
tar -xzf gearman-1.0.3.tgz
cd gearman-1.0.3
phpize
./configure
make
make install

# Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# PHPUnit
pear config-set auto_discover 1
pear install pear.phpunit.de/PHPUnit
