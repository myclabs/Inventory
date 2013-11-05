#!/usr/bin/env php
<?php

use Doctrine\ORM\EntityManager;
use Orga\Model\ACL\Role\CellAdminRole;
use Orga\Model\ACL\Role\CellContributorRole;
use Orga\Model\ACL\Role\CellObserverRole;
use Orga\Model\ACL\Role\OrganizationAdminRole;
use User\Domain\ACL\Role\AdminRole;
use User\Domain\ACL\Role\UserRole;
use User\Domain\User;

define('RUN', false);
require_once __DIR__ . '/../../../application/init.php';

$configuration = Zend_Registry::get('configuration');
$connectionSettings = $configuration->doctrine->default->connection;
$mysqlBin = $configuration->mysqlBin->path;
$dbHost = $connectionSettings->host;
$db = $connectionSettings->dbname;
$dbUser = $connectionSettings->user;
$dbPassword = $connectionSettings->password;

// Administrator
$query = <<<SQL
SELECT DISTINCT idUser
FROM User_UserRoles ur
  INNER JOIN User_Role r ON r.id = ur.idRole
WHERE ref = 'sysadmin';
SQL;
$adminRoles = execQuery($query);

// Organization admin
$query = <<<SQL
SELECT DISTINCT idUser, SUBSTRING_INDEX(SUBSTRING_INDEX(r.ref, '_', 2), '_', -1) as idOrganization
FROM User_UserRoles ur
  INNER JOIN User_Role r ON r.id = ur.idRole
WHERE name = 'organizationAdministrator';
SQL;
$organizationAdminRoles = execQuery($query);

// Cell admin
$query = <<<SQL
SELECT DISTINCT idUser, SUBSTRING_INDEX(SUBSTRING_INDEX(r.ref, '_', 2), '_', -1) as idCell
FROM User_UserRoles ur
  INNER JOIN User_Role r ON r.id = ur.idRole
WHERE name = 'cellAdministrator';
SQL;
$cellAdminRoles = execQuery($query);

// Cell contributor
$query = <<<SQL
SELECT DISTINCT idUser, SUBSTRING_INDEX(SUBSTRING_INDEX(r.ref, '_', 2), '_', -1) as idCell
FROM User_UserRoles ur
  INNER JOIN User_Role r ON r.id = ur.idRole
WHERE name = 'cellContributor';
SQL;
$cellContributorRoles = execQuery($query);

// Cell observer
$query = <<<SQL
SELECT DISTINCT idUser, SUBSTRING_INDEX(SUBSTRING_INDEX(r.ref, '_', 2), '_', -1) as idCell
FROM User_UserRoles ur
  INNER JOIN User_Role r ON r.id = ur.idRole
WHERE name = 'cellObserver';
SQL;
$cellObserverRoles = execQuery($query);


// Run build update to update DB
echo "Executing build update" . PHP_EOL . PHP_EOL;
$output = [];
$return = 0;
exec('php ../../build/build.php update', $output, $return);
if ($return !== 0) {
    die("Error executing build update" . PHP_EOL . implode(PHP_EOL, $output));
}


/** @var \DI\Container $container */
$container = Zend_Registry::get('container');
/** @var EntityManager $em */
$em = $container->get(EntityManager::class);


// User
foreach (User::loadList() as $user) {
    /** @var User $user */
    echo "Adding UserRole to {$user->getEmail()}" . PHP_EOL;
    $user->addRole(new UserRole($user));
    $user->save();
}
echo PHP_EOL;

// Administrator
foreach ($adminRoles as list($idUser)) {
    $user = User::load($idUser);
    echo "Adding AdminRole to {$user->getEmail()}" . PHP_EOL;
    $user->addRole(new AdminRole($user));
    $user->save();
}
echo PHP_EOL;

// Organization admin
foreach ($organizationAdminRoles as list($idUser, $idOrganization)) {
    $user = User::load($idUser);
    $organization = Orga_Model_Organization::load($idOrganization);
    echo "Adding OrganizationAdminRole to {$user->getEmail()} for organization $idOrganization" . PHP_EOL;
    $user->addRole(new OrganizationAdminRole($user, $organization));
    $user->save();
}
echo PHP_EOL;

// Cell admin
foreach ($cellAdminRoles as list($idUser, $idCell)) {
    $user = User::load($idUser);
    $cell = Orga_Model_Cell::load($idCell);
    echo "Adding CellAdminRole to {$user->getEmail()} for cell $idCell" . PHP_EOL;
    $user->addRole(new CellAdminRole($user, $cell));
    $user->save();
}
echo PHP_EOL;

// Cell contributor
foreach ($cellContributorRoles as list($idUser, $idCell)) {
    $user = User::load($idUser);
    echo "Adding CellContributorRole to {$user->getEmail()} for cell $idCell" . PHP_EOL;
    $user->addRole(new CellContributorRole($user, $cell));
    $user->save();
}
echo PHP_EOL;

// Cell observer
foreach ($cellObserverRoles as list($idUser, $idCell)) {
    $user = User::load($idUser);
    echo "Adding CellObserverRole to {$user->getEmail()} for cell $idCell" . PHP_EOL;
    $user->addRole(new CellObserverRole($user, $cell));
    $user->save();
}
echo PHP_EOL;

$em->flush();


function execQuery($query)
{
    global $mysqlBin, $dbHost, $dbUser, $dbPassword, $db;
    $exportCommand = <<<BASH
$mysqlBin -h $dbHost -u $dbUser --password=$dbPassword --skip-column-names $db <<QUERY
$query
QUERY
BASH;

    $output = [];
    $return = 0;
    exec($exportCommand, $output, $return);
    if ($return !== 0) {
        die("Error executing:" . PHP_EOL . $exportCommand . PHP_EOL . implode(PHP_EOL, $output));
    }

    $output = array_map(
        function ($str) {
            return explode("\t", $str);
        },
        $output
    );

    return $output;
}
