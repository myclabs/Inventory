<?php
/**
 * Scripts de build
 */

set_time_limit(0);

define('RUN', false);

require_once __DIR__ . '/../../application/init.php';

/**
 * Lance le script
 */
$script = new Core_Script_Build();
$script->run();
