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

// Deal with the foreign keys before the doctrine update
$query = <<<SQL
ALTER TABLE AuditTrail_Entry DROP FOREIGN KEY FK_41462EB8A76ED395;
ALTER TABLE Simulation_Set DROP FOREIGN KEY FK_E11EE25CFE6E88D7;
ALTER TABLE Social_Comment DROP FOREIGN KEY FK_19D6C6B5DEBE7052;
ALTER TABLE Social_Message DROP FOREIGN KEY FK_3B1FA4A6DEBE7052;
ALTER TABLE Social_Message_User_Recipients DROP FOREIGN KEY FK_8DB9A0B7FE6E88D7;
ALTER TABLE Social_News DROP FOREIGN KEY FK_746C6ADDDEBE7052;
ALTER TABLE Social_UserGroup_Users DROP FOREIGN KEY FK_D8A9E317FE6E88D7;
ALTER TABLE User_UserRoles DROP FOREIGN KEY FK_1F2E4A8EFE6E88D7;
ALTER TABLE User_User DROP FOREIGN KEY FK_D5D1B71DBF396750;
ALTER TABLE User_User CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE locale locale VARCHAR(255) DEFAULT NULL;
ALTER TABLE User_User ADD CONSTRAINT FK_D5D1B71DBF396750 FOREIGN KEY (id) REFERENCES User_SecurityIdentity (id) ON DELETE CASCADE;
ALTER TABLE AuditTrail_Entry ADD CONSTRAINT FK_41462EB8A76ED395 FOREIGN KEY (user_id) REFERENCES User_User (id);
ALTER TABLE Simulation_Set ADD CONSTRAINT FK_E11EE25CFE6E88D7 FOREIGN KEY (idUser) REFERENCES User_User (id);
ALTER TABLE Social_Comment ADD CONSTRAINT FK_19D6C6B5DEBE7052 FOREIGN KEY (idAuthor) REFERENCES User_User (id);
ALTER TABLE Social_Message ADD CONSTRAINT FK_3B1FA4A6DEBE7052 FOREIGN KEY (idAuthor) REFERENCES User_User (id);
ALTER TABLE Social_Message_User_Recipients ADD CONSTRAINT FK_8DB9A0B7FE6E88D7 FOREIGN KEY (idUser) REFERENCES User_User (id);
ALTER TABLE Social_News ADD CONSTRAINT FK_746C6ADDDEBE7052 FOREIGN KEY (idAuthor) REFERENCES User_User (id);
ALTER TABLE Social_UserGroup_Users ADD CONSTRAINT FK_D8A9E317FE6E88D7 FOREIGN KEY (idUser) REFERENCES User_User (id);
ALTER TABLE User_UserRoles ADD CONSTRAINT FK_1F2E4A8EFE6E88D7 FOREIGN KEY (idUser) REFERENCES User_User (id);
SQL;
execQuery($query);

function execQuery($query)
{
    global $mysqlBin, $dbHost, $dbUser, $dbPassword, $db;
    $onlineQuery = trim(preg_replace('/\s+/', ' ', $query));
    $exportCommand = <<<BASH
$mysqlBin -h $dbHost -u $dbUser --password=$dbPassword --skip-column-names $db -e "$onlineQuery"
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
