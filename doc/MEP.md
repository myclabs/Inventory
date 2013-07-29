# Mise en production

Rappel : pour les MEP, il est déconseillé d'utiliser `build update`.

## 2.3

- Installer APC

APC est déjà installé en `test`.

```
sudo pecl install apc
sudo nano /etc/php5/apache2/php.ini
sudo apachectl restart
```

- Définir l'environnement d'exécution

```
sudo cp application/configs/env.php.default application/configs/env.php
sudo nano env.php
```

- Exécuter le script de migration SQL

```
scripts/migration/2.3/migrate.sql
```

- Exécuter le script de migration PHP

```
sudo php scripts/migration/2.3/migrate.php
```

- Exécuter le script de suppression des indexations d'algos inutiles

```
sudo php scripts/af-indexation/fix-af-indexation.php
```
