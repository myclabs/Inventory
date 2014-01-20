#!/bin/sh
# Setup the application

set -e

cat > application/configs/application.ini <<EOL
[production]
applicationUrl=http://localhost/inventory
[test : production]
[developpement : test]
[testsunitaires : test]
EOL

cat > application/configs/env.php <<EOL
<?php
/**
 * Environnement d'execution
 */

defined('APPLICATION_ENV') || define('APPLICATION_ENV', 'testsunitaires');
EOL

composer install --no-progress
