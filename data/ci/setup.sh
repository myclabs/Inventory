#!/bin/sh
# Setup the application

set -e

cat > application/configs/parameters.php <<EOL
<?php
return [
    'application.url' => 'whatever',
];
EOL

cat > application/configs/env.php <<EOL
<?php
/**
 * Environnement d'execution
 */

defined('APPLICATION_ENV') || define('APPLICATION_ENV', 'testsunitaires');
EOL

sudo composer selfupdate
composer install --no-progress
