# Mise en production


## 3.0

- Migration des traductions (noms des classes) suite au renommage en namespaces de Classif : TODO

- Migration des unités des indicateurs (colonne string to unit_api) : TODO

- Renommage des tables de Techno en Parameter : TODO


## 2.11

- Déployer l'application **sans build update ni redémarrage du worker**

- Copier `application/configs/parameters.php.default` vers `application/configs/parameters.php`

- Configurer `application/configs/parameters.php` en s'inspirant du `application.ini`

- Faire un build update et redémarrer le worker.


## 2.10

- Renomer l'éventuel dossier data/specificExports en data/specificReports

- Déployer normalement l'application **sans build update**

- Exécuter le script de migration SQL

```
scripts/migration/2.10/migrate.sql
```

- Faire le build update

```
php scripts/build/build.php update
```

- Exécuter le script de migration

```
php scripts/migration/2.10/migrate.php
```

- Faire un rebuild de DW (pour regénérer les traductions)

- Lancer le script de rebuild des ACL

```
php scripts/jobs/acl/rebuild.php acl:rebuild
```

- Si il reste des traductions de DW dans `ext_translations`, les supprimer


## 2.9

- Installer memcached et le plugin New Relic (cf. projet server)

- Modifier le php.ini apache (`/etc/php5/apache2/php.ini`) pour utiliser Memcached pour les sessions :

```
session.save_handler = memcached
...
session.save_path = "localhost:11211"
```

- Mettre à jour le script `deploy` (`git pull` dans `/home/deploy`)

- Déployer l'application SANS build update ni redémarrage du worker

```
sudo deploy 2.9.?
```

- Exécuter le script de migration

```
php scripts/migration/2.9/migrate.php
```

Le script va effectuer un build update

- Exécuter le job de rebuild des exports (long)

```
php scripts/jobs/exports/rebuild.php
```

- En cas de dépassement de mémoire, ré-exécuter le script avec l'option --no-clear (ou -c)

```
php scripts/jobs/exports/rebuild.php -c
```

- Redémarrer le worker

```
sudo supervisorctl restart XXX-worker
```


## 2.8

- Déployer normalement l'application (avec build update)

- Exécuter le script de migration SQL

```
scripts/migration/2.8/migrate.sql
```


## 2.7

- Mettre hors ligne la prod actuelle (sauf stations de montagne et spiritueux)

- Installer les projets sur le nouveau serveur

- Copier les BDD

- Copier les fichiers
  - `data/documents`
  - `data/specificExports`
  - `public/temp` ?

- Exécuter le script de migration SQL

```
scripts/migration/2.7/migrate.sql
```

- Déployer la version avec build update

- Configurer les noms de domaine pour pointer vers le nouveau serveur


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
