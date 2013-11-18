<?php

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
$cellManagersRoles = execQuery($query);

// Cell observer
$query = <<<SQL
SELECT DISTINCT idUser, SUBSTRING_INDEX(SUBSTRING_INDEX(r.ref, '_', 2), '_', -1) as idCell
FROM User_UserRoles ur
  INNER JOIN User_Role r ON r.id = ur.idRole
WHERE name = 'cellObserver';
SQL;
$cellObserverRoles = execQuery($query);


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
