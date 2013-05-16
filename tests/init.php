<?php

/**
 * Environnement d'exécution de l'application
 * @see http://dev.myc-sense.com/wiki/index.php/Environnement_d%27ex%C3%A9cution
 * @var string
 */
define('APPLICATION_ENV', 'testsunitaires');

/**
 * Détermine si l'application est lancée après le Bootstrap
 * @var bool
 */
define('RUN', false);

require_once __DIR__ . '/../application/init.php';
