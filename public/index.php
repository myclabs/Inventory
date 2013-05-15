<?php

/**
 * Environnement d'exécution de l'application
 * @see http://dev.myc-sense.com/wiki/index.php/Environnement_d%27ex%C3%A9cution
 * @var string
 */
define('APPLICATION_ENV', 'developpement');

/**
 * Détermine si l'application est lancée après le Bootstrap
 * @var bool
 */
define('RUN', true);

require_once __DIR__ . '/../application/init.php';
