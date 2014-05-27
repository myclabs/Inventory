# Mise en production


## 3.1

- Déployer normalement avec update de la bdd.

```

- Mettre à jour le cache d'orga :

```
bin/inventory orga-cache:rebuild
```


## 3.0

- Exporter les données en 2.12

```
sudo chmod 777 data/exports/migration-3.0/
bin/inventory export -v
```

Les données sont exportées dans `data/exports/migration-3.0/`.

- Déployer l'application en v3.0 **sans build update** (mais redémarrer le worker) :

```
sudo deploy --no-update-db 3.0.?
```

- Reconstruire la BDD from scratch :

```
bin/inventory db:populate
```

- Créer un nouveau compte client si besoin

```
bin/inventory account:create "Nom du compte"
```

- Réimporter les données

Les bibliothèques seront créées en utilisant les noms donnés en ligne de commande, et ajoutées au compte donné.

Les données seront importées des fichiers contenus dans `data/exports/migration-3.0/`.

```
bin/inventory import <id-account> "Bibliothèque de classification" "Bibliothèque de paramètres" "Bibliothèque de formulaires" -v
```

- Exécuter le job de rebuild des exports (long)

```
bin/inventory export:rebuild
```

- En cas de dépassement de mémoire, ré-exécuter le script avec l'option --no-clear (ou -c)

```
bin/inventory export:rebuild --no-clear
```

- Reconstruire les ACL

```
bin/inventory acl:rebuild
```

- Vider la table des versions

```
TRUNCATE TABLE ext_log_entries
```


## 2.11

- Déployer l'application **sans build update ni redémarrage du worker**

- Exécuter le script de migration SQL

```
scripts/migration/2.11/migrate.sql
```

- Copier `application/configs/parameters.php.default` vers `application/configs/parameters.php`

- Configurer `application/configs/parameters.php` en s'inspirant du `application.ini`

- Faire un build update et redémarrer le worker.

- Lancer le script de rebuild des Exports

```
bin/inventory export:rebuild
```


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

```
DELETE FROM `ext_translations` WHERE object_class="DW_Model_Axis" OR object_class="DW_Model_Axis" OR object_class="DW_Model_Member" OR object_class="DW_Model_Indicator" OR object_class="DW_Model_Report" OR object_class="DW_Model_Cube"
```


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
