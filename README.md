# Inventory

## Installation


Checkout the project using git or the Github plugin for PhpStorm.

Install [composer](http://getcomposer.org/doc/00-intro.md) and run:

```bash
composer install
```

Set up config files

```bash
cp application/configs/application.ini.default application/configs/application.ini
cp public/.htaccess.default public/.htaccess
```

Set up file rights

```bash
chmod 777 data/documents
chmod 777 data/logs
chmod 777 data/proxies
chmod -R 777 public/cache
chmod 777 public/temp
```

## Run with Vagrant

Install [Virtual Box](https://www.virtualbox.org/wiki/Downloads) and [Vagrant](http://www.vagrantup.com/):

```bash
vagrant up
```

The website is accessible at [http://localhost:8080/](http://localhost:8080/).

PhpMyAdmin is accessible at [http://localhost:8080/phpmyadmin/](http://localhost:8080/phpmyadmin/).

SSH to the virtual machine:

```bash
vagrant ssh
cd /vagrant
```

Destroy the VM:

```bash
vagrant destroy
```

### Tests

```bash
phpunit
```
