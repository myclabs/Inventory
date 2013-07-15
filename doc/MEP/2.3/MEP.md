# Mise en production v2.3

- Installer APC

APC est déjà installé en `test`.

    sudo pecl install apc
    sudo nano /etc/php5/apache2/php.ini
    sudo apachectl restart

- Exécuter le script de suppression des indexations d'algos inutiles

    sudo php scripts/af-indexation/fix-af-indexation.php
