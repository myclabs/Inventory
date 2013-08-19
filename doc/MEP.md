# Mise en production

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
