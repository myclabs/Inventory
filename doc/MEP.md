# Mise en production


## 2.7

- Désinstaller Gearman (cf. la procédure d'installation sur le wiki)

- Installer RabbitMQ

```shell
$ sudo su
$ echo "deb http://www.rabbitmq.com/debian/ testing main" > /etc/apt/sources.list.d/rabbitmq.list
$ wget http://www.rabbitmq.com/rabbitmq-signing-key-public.asc
$ apt-key add rabbitmq-signing-key-public.asc
$ apt-get update
$ apt-get install -y rabbitmq-server
```

- Mettre à jour les confs supervisor (`/etc/supervisor/conf.d/*.conf`) pour ajouter :

```
autorestart=true
```

puis exécuter :

```shell
$ sudo supervisorctl reload
```

- Déployer l'application sans build update

- Exécuter le script de migration SQL

```
scripts/migration/2.7/migrate.sql
```

- Effectuer un build update


## 2.5

- Déployer normalement l'application (avec build update)

- Exécuter le script de migration SQL

```
scripts/migration/2.5/migrate.sql
```


## 2.4

- Déployer normalement l'application (avec build update)

- Exécuter le script de migration PHP

```
sudo php scripts/migration/2.4/migrate.php
```

- Relancer tous les calculs via l'interface


## 2.3

- Installer APC

APC est déjà installé en `test`.

```
sudo pecl install apc
sudo nano /etc/php5/apache2/php.ini
sudo nano /etc/php5/cli/php.ini
sudo apachectl restart
```

- Déployer sans update ou redémarrage du worker

```
sudo deploy 2.3.?
```

- Définir l'environnement d'exécution

```
sudo cp application/configs/env.php.default application/configs/env.php
sudo nano application/configs/env.php
```

- Exécuter le script de migration SQL

```
scripts/migration/2.3/migrate.sql
```

- Faire le build update

```
php scripts/build/build.php update
```

- Exécuter le script de migration PHP

```
sudo php scripts/migration/2.3/migrate.php
```

- MAJ des droits sur le log des requêtes

```
sudo chmod 777 data/logs/queries.log
```

- Exécuter le script de suppression des indexations d'algos inutiles

```
sudo php scripts/af-indexation/fix-af-indexation.php
```

- Redémarrer le worker

```
sudo supervisorctl
    restart inventory-worker
    status
```
