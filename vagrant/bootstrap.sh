#!/bin/bash

apt-get update

# For PHP 5.4
apt-get install -y python-software-properties
add-apt-repository -y ppa:ondrej/php5
apt-get update

apt-get install -y bash-completion curl lynx

# SCM
apt-get install -y git

# Shell
apt-get install -y zsh
git clone git://github.com/robbyrussell/oh-my-zsh.git /home/vagrant/.oh-my-zsh
chsh -s $(which zsh) vagrant
cp /vagrant/vagrant/.zshrc /home/vagrant/

# Mysql
export DEBIAN_FRONTEND=noninteractive
apt-get install -q -y mysql-server

# PHP
apt-get install -y php5 php5-curl php5-cli php5-gd php5-mcrypt php5-dev php5-mysql php-pear

# Apache
apt-get install -y apache2
rm -rf /var/www
ln -fs /vagrant/public /var/www
cp /vagrant/vagrant/php.ini /etc/php5/apache2/

# phpMyAdmin
export DEBIAN_FRONTEND=noninteractive
echo 'phpmyadmin phpmyadmin/dbconfig-install boolean true' | debconf-set-selections
echo 'phpmyadmin phpmyadmin/app-password-confirm password ' | debconf-set-selections
echo 'phpmyadmin phpmyadmin/mysql/admin-pass password ' | debconf-set-selections
echo 'phpmyadmin phpmyadmin/mysql/app-pass password ' | debconf-set-selections
echo 'phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2' | debconf-set-selections
apt-get install -q -y phpmyadmin
