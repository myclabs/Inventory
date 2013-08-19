# Inventory

## Installation

Checkout the project using git or the Github plugin for PhpStorm.

Install [composer](http://getcomposer.org/doc/00-intro.md) and run:

```shell
composer install
```

Set up config files

```shell
cp application/configs/application.ini.default application/configs/application.ini
cp public/.htaccess.default public/.htaccess
cp application/configs/env.php.default application/configs/env.php
nano application/configs/env.php
```

Set up file rights

```shell
chmod 777 data/documents
chmod -R 777 data/logs
chmod 777 data/proxies
chmod -R 777 public/cache
chmod 777 public/temp
```

## Run with Vagrant

Install [Virtual Box](https://www.virtualbox.org/wiki/Downloads) and [Vagrant](http://www.vagrantup.com/):

```shell
vagrant up
```

The website is accessible at [http://localhost:8000/inventory/](http://localhost:8000/inventory/).

PhpMyAdmin is accessible at [http://localhost:8000/phpmyadmin/](http://localhost:8000/phpmyadmin/).

SSH to the virtual machine:

```shell
vagrant ssh
cd /vagrant
```

Destroy the VM:

```shell
vagrant destroy
```

### Tests

```shell
phpunit
```
