<?php
/**
 * Migre les Calc_Value et Calc_UnitValue stockés en BDD
 */

define('APPLICATION_ENV', 'script');
define('RUN', false);

require_once __DIR__ . '/../../../application/init.php';

require __DIR__ . '/migrateTranslations.php';

require __DIR__ . '/migrateTechno.php';

require __DIR__ . '/migrateDW.php';
