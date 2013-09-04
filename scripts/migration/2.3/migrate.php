<?php
/**
 * Migre les Calc_Value et Calc_UnitValue stockés en BDD
 */

define('RUN', false);

require_once __DIR__ . '/../../../application/init.php';

echo "Creating missing units..." . PHP_EOL;
require 'createMissingUnits.php';
echo PHP_EOL;

echo "Migrating UnitValues..." . PHP_EOL;
require 'migrateCalcUnitValue.php';
echo PHP_EOL;
