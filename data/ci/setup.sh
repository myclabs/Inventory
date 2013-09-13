#!/bin/sh
# Setup the application

cat > application/configs/application.ini <<EOL
[production]
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
