#!/bin/bash

BASEDIR=/vagrant/data/vagrant

apt-get update

# For PHP 5.5
apt-get install -y python-software-properties
add-apt-repository -y ppa:ondrej/php5

# RabbitMQ
echo "deb http://www.rabbitmq.com/debian/ testing main" > /etc/apt/sources.list.d/rabbitmq.list
wget http://www.rabbitmq.com/rabbitmq-signing-key-public.asc
apt-key add rabbitmq-signing-key-public.asc

apt-get update

apt-get install -y curl git rabbitmq-server zsh

# Mysql
export DEBIAN_FRONTEND=noninteractive
apt-get install -q -y mysql-server
mysql -u root -e "CREATE USER 'myc-sense'@'localhost' IDENTIFIED BY '';"
mysql -u root -e "GRANT ALL PRIVILEGES ON *.* TO 'myc-sense'@'localhost';"
mysql -u root -e "FLUSH PRIVILEGES;"

# PHP
apt-get install -y php5 php5-curl php5-cli php5-gd php5-mcrypt php5-dev php5-mysql php-pear php5-xdebug

# Apache
apt-get install -y apache2
ln -s /vagrant/public /var/www/inventory
cp ${BASEDIR}/php.ini /etc/php5/apache2/
cp ${BASEDIR}/php.ini /etc/php5/cli/
cp ${BASEDIR}/apache-000-default /etc/apache2/sites-enabled/000-default.conf
a2enmod rewrite
apachectl restart

# phpMyAdmin
export DEBIAN_FRONTEND=noninteractive
echo 'phpmyadmin phpmyadmin/dbconfig-install boolean true' | debconf-set-selections
echo 'phpmyadmin phpmyadmin/app-password-confirm password ' | debconf-set-selections
echo 'phpmyadmin phpmyadmin/mysql/admin-pass password ' | debconf-set-selections
echo 'phpmyadmin phpmyadmin/mysql/app-pass password ' | debconf-set-selections
echo 'phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2' | debconf-set-selections
apt-get install -q -y phpmyadmin
cp ${BASEDIR}/phpmyadmin-config.inc.php /etc/phpmyadmin/config.inc.php

# Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# PHPUnit
pear config-set auto_discover 1
pear install pear.phpunit.de/PHPUnit

# Behat & Selenium
#add-apt-repository -y ppa:mozillateam/firefox-stable
#apt-get install -y xvfb xfonts-100dpi xfonts-75dpi xfonts-scalable xfonts-cyrillic
#apt-get install -y default-jre-headless
#apt-get install -y firefox
#echo 'user_pref("intl.accept_languages", "fr, fr-fr, en-us, en");' >> /etc/firefox/syspref.js

#apt-get install -y chromium-browser
#wget -O /tmp/chromedriver.zip https://chromedriver.googlecode.com/files/chromedriver_linux32_2.0.zip
#unzip /tmp/chromedriver.zip -d /tmp
#mv /tmp/chromedriver /usr/local/bin/

#apt-get install -y nodejs npm
#npm install -g zombie@1.4.1
