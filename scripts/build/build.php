<?php
/**
 * Scripts de build
 */

use Core\Script\Build;

define('RUN', false);

require_once __DIR__ . '/../../vendor/autoload.php';

$script = new Build();

require_once __DIR__ . '/../../application/init.php';

$script->run();
