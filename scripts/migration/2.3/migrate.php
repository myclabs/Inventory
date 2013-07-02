<?php
/**
 * Migre les Calc_Value et Calc_UnitValue stockés en BDD
 */

define('APPLICATION_ENV', 'script');
define('RUN', false);

require_once __DIR__ . '/../../../application/init.php';

require_once 'migrateCalcUnitValue.php';
