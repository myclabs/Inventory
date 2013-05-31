<?php
/**
 * @package Inventory
 */


/**
 * @package Inventory
 */
class Inventory_Migrate extends Core_Script_Populate
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var PDO
     */
    private $connection;

    private $mapIdProject = array();
    private $mapIdAxes = array();
    private $mapIdMembers = array();
    private $mapIdGranularities = array();


    /**
     * @param string $idProject
     * @return Orga_Model_Project
     * @throws Core_Exception_NotFound
     */
    protected function getProject($idProject)
    {
        if (!isset($this->mapIdProject[$idProject])) {
            throw new Core_Exception_NotFound();
        }
        if (!($this->mapIdProject[$idProject] instanceof Orga_Model_Project)) {
            $this->mapIdProject[$idProject] = Orga_Model_Project::load($this->mapIdProject[$idProject]);
        }
        return $this->mapIdProject[$idProject];
    }

    /**
     * @param string $idAxis
     * @return Orga_Model_Axis
     * @throws Core_Exception_NotFound
     */
    protected function getAxis($idAxis)
    {
        if (!isset($this->mapIdAxes[$idAxis])) {
            throw new Core_Exception_NotFound();
        }
        if (!($this->mapIdAxes[$idAxis] instanceof Orga_Model_Axis)) {
            $this->mapIdAxes[$idAxis] = Orga_Model_Axis::load($this->mapIdAxes[$idAxis]);
        }
        return $this->mapIdAxes[$idAxis];
    }

    /**
     * @param string $idMember
     * @return Orga_Model_Member
     * @throws Core_Exception_NotFound
     */
    protected function getMember($idMember)
    {
        if (!isset($this->mapIdMembers[$idMember])) {
            throw new Core_Exception_NotFound();
        }
        if (!($this->mapIdMembers[$idMember] instanceof Orga_Model_Member)) {
            $this->mapIdMembers[$idMember] = Orga_Model_Member::load($this->mapIdMembers[$idMember]);
        }
        return $this->mapIdMembers[$idMember];
    }

    /**
     * @param string $idGranularity
     * @return Orga_Model_Granularity
     * @throws Core_Exception_NotFound
     */
    protected function getGranularity($idGranularity)
    {
        if (!isset($this->mapIdGranularities[$idGranularity])) {
            throw new Core_Exception_NotFound();
        }
        if (!($this->mapIdGranularities[$idGranularity] instanceof Orga_Model_Granularity)) {
            $this->mapIdGranularities[$idGranularity] = Orga_Model_Granularity::load(
                $this->mapIdGranularities[$idGranularity]
            );
        }
        return $this->mapIdGranularities[$idGranularity];
    }

    /**
     * Init
     * @param string $dbName
     */
    protected function init($dbName)
    {
        // BDD
        $connectionSettings = Zend_Registry::get('configuration')->doctrine->default->connection;
        $host = $connectionSettings->host;
        $user = $connectionSettings->user;
        $password = $connectionSettings->password;
        $url = "mysql:host=$host;dbname=$dbName";
        $options = [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"];
        $this->connection = new PDO($url, $user, $password, $options);
        $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $this->em->clear();
        $this->mapIdProject = array();
        $this->mapIdAxes = array();
        $this->mapIdMembers = array();
        $this->mapIdGranularities = array();
    }

    /**
     *
     */
    public function flush()
    {
        echo "\t ---> flush starting…";

        $this->em->flush();

        foreach ($this->mapIdProject as $idCube => $cube) {
            if ($cube instanceof Orga_Model_Project) {
                $this->mapIdProject[$idCube] = $cube->getKey();
            }
        }
        foreach ($this->mapIdAxes as $idAxis => $axis) {
            if ($axis instanceof Orga_Model_Axis) {
                $this->mapIdAxes[$idAxis] = $axis->getKey();
            }
        }
        foreach ($this->mapIdMembers as $idMember => $member) {
            if ($member instanceof Orga_Model_Member) {
                $this->mapIdMembers[$idMember] = $member->getKey();
            }
        }
        foreach ($this->mapIdGranularities as $idGranularity => $granularity) {
            if ($granularity instanceof Orga_Model_Granularity) {
                $this->mapIdGranularities[$idGranularity] = $granularity->getKey();
            }
        }

        $this->em->clear();

        echo "\t <--- end flush !\n";
    }


    /**
     * Populate a specific environment
     * @param string $environment
     */
    protected function populateEnvironment($environment)
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        $this->em = $entityManagers['default'];
        $events = [
            Doctrine\ORM\Events::postFlush,
        ];
        $this->em->getEventManager()->addEventListener($events, Inventory_Service_ACLManager::getInstance());


        echo "Début de la migration -->\n";

        $this->migrateDBs(
            array(
                'inventory_old',
            )
        );
    }

    /**
     * @param array $dbs
     */
    protected function migrateDBs($dbs)
    {
        foreach ($dbs as $dbName) {
            $this->processBDD($dbName);
        }
    }

    /**
     * @param string $dbName
     */
    protected function processBDD($dbName)
    {
        try {
            $this->init($dbName);
            echo " _ $dbName _\n";
            $this->migrateProjects();
            //            $this->migrateUserRoles();
        } catch (PDOException $e) {
            echo " - aborting : $dbName _ La base n'existe pas.\n";
        }
    }

    /**
     *
     */
    protected function migrateProjects()
    {
        $select = $this->connection->query("SELECT * FROM Inventory_Project");
        /** @noinspection PhpAssignmentInConditionInspection */
        while ($row = $select->fetch()) {
            $this->processProject($row);
        }
        $select->closeCursor();
    }

    /**
     * @param array $row
     */
    protected function processProject($row)
    {
        echo "\t Project : " . $row['id'] . "\n";
        $project = new Orga_Model_Project();
        $project->setLabel($row['label']);
        $project->save();

        $this->mapIdProject[$row['idOrgaCube']] = $project;
        $this->flush();

        $this->migrateProjectAxes($row['idOrgaCube']);

        $this->migrateProjectGranularities($row['idOrgaCube']);

        // Nécessaire à cause des clear.
        $project = $this->getProject($row['idOrgaCube']);
        if ($row['idOrgaGranularityForInventoryStatus'] != null) {
            $project->setGranularityForInventoryStatus(
                $this->getGranularity($row['idOrgaGranularityForInventoryStatus'])
            );
            echo "\t\t > granularité des inventaires : " . $this->getGranularity(
                    $row['idOrgaGranularityForInventoryStatus']
                )->getLabel() . "\n";
            $this->flush();
        } else {
            echo "\t\t > pas de granularité des inventaires \n";
        }

        $this->migrateProjectAFConfig($row['idOrgaCube']);

        $this->migrateProjectGranularityDataProviders($row['idOrgaCube']);

        echo "\t\t > regénération des données des cubes\n";
        Orga_Service_ETLStructure::getInstance()->resetProjectDWCubes($this->getProject($row['idOrgaCube']));
        $this->flush();
    }


    /**
     * @param int $idCube
     */
    protected function migrateProjectAxes($idCube)
    {
        $select = $this->connection->query(
            "SELECT * FROM Orga_Axis WHERE idCube=$idCube AND idDirectNarrower IS NULL ORDER BY id"
        );
        /** @noinspection PhpAssignmentInConditionInspection */
        while ($row = $select->fetch()) {
            $this->processAxis($row);
        }
        $select->closeCursor();
        $this->flush();
    }

    /**
     * @param array $row
     */
    protected function processAxis($row)
    {
        $axis = new Orga_Model_Axis($this->getProject($row['idCube']));
        $axis->setRef($row['ref']);
        $axis->setLabel($row['label']);
        if ($row['idDirectNarrower'] != null) {
            $axis->setDirectNarrower($this->getAxis($row['idDirectNarrower']));
        }
        $axis->save();

        echo "\t\t Axis : " . $row['ref'] . (($row['idDirectNarrower'] != null) ? " narrower of " . $this->getAxis(
                    $row['idDirectNarrower']
                )->getRef() : "") . "\n";
        $this->mapIdAxes[$row['id']] = $axis;

        $this->migrateAxisMembers($row['id']);
        $this->migrateAxisBroaders($row['idCube'], $row['id']);
    }

    /**
     * @param int $idAxis
     */
    protected function migrateAxisMembers($idAxis)
    {
        // Member
        $select = $this->connection->query("SELECT * FROM Orga_Member WHERE idAxis=$idAxis ORDER BY id");
        /** @noinspection PhpAssignmentInConditionInspection */
        while ($row = $select->fetch()) {
            $this->processMember($row, $idAxis);
        }
        $select->closeCursor();
    }

    /**
     * @param array $row
     */
    protected function processMember($row)
    {
        echo "\t\t\t Member : " . $row['ref'] . "\n";

        $member = new Orga_Model_Member($this->getAxis($row['idAxis']));
        $member->setRef($row['ref']);
        $member->setLabel($row['label']);
        $member->save();

        $this->mapIdMembers[$row['id']] = $member;

        $this->migrateChildMembers($row['id']);
    }

    /**
     * @param int $idMember
     */
    protected function migrateChildMembers($idMember)
    {
        $subSelect = $this->connection->query(
            "SELECT * FROM Orga_Member_Association WHERE idParent=$idMember ORDER BY idChild"
        );
        /** @noinspection PhpAssignmentInConditionInspection */
        while ($row = $subSelect->fetch()) {
            $this->processChildMember($row);
        }
        $subSelect->closeCursor();
    }

    /**
     * @param array $row
     */
    protected function processChildMember($row)
    {
        echo "\t\t\t\t > associated with : " . $this->getMember($row['idChild'])->getRef() . "\n";

        $this->getMember($row['idParent'])->addDirectChild($this->getMember($row['idChild']));
    }

    /**
     * @param int $idCube
     * @param int $idAxis
     */
    protected function migrateAxisBroaders($idCube, $idAxis)
    {
        $select = $this->connection->query(
            "SELECT * FROM Orga_Axis WHERE idCube=$idCube AND idDirectNarrower=$idAxis ORDER BY id"
        );
        /** @noinspection PhpAssignmentInConditionInspection */
        while ($row = $select->fetch()) {
            $this->processAxis($row, $idCube);
        }
        $select->closeCursor();
    }

    /**
     * @param int $idCube
     */
    protected function migrateProjectGranularities($idCube)
    {
        $select = $this->connection->query("SELECT * FROM Orga_Granularity WHERE idCube=$idCube");
        /** @noinspection PhpAssignmentInConditionInspection */
        while ($row = $select->fetch()) {
            $this->processGranularity($row);
        }
        $select->closeCursor();
        $this->flush();
    }

    /**
     * @param array $row
     */
    protected function processGranularity($row)
    {
        // Axes association
        $subSelect = $this->connection->query("SELECT * FROM Orga_Granularity_Axis WHERE idGranularity=" . $row['id']);
        $axes = array();
        /** @noinspection PhpAssignmentInConditionInspection */
        while ($rowAssociation = $subSelect->fetch()) {
            $axes[] = $this->getAxis($rowAssociation['idAxis']);
        }
        $subSelect->closeCursor();

        $granularity = new Orga_Model_Granularity($this->getProject($row['idCube']), $axes);
        $granularity->setNavigability((bool)$row['navigable']);
        $granularity->save();

        echo "\t\t Granularity (" . count($axes) . ") " . $granularity->getRef() . " : " . $row['ref'] . "\n";
        $this->mapIdGranularities[$row['id']] = $granularity;
        foreach ($granularity->getAxes() as $axis) {
            echo "\t\t\t > using axis : " . $axis->getRef() . "\n";
        }
        $this->flush();

        $this->migrateGranularityCells($row['id']);
        $this->flush();
    }

    /**
     * @param int $idGranularity
     */
    protected function migrateGranularityCells($idGranularity)
    {
        $select = $this->connection->query("SELECT * FROM Orga_Cell WHERE idGranularity=$idGranularity");
        /** @noinspection PhpAssignmentInConditionInspection */
        while ($row = $select->fetch()) {
            $this->processCell($row);
        }
        $select->closeCursor();
    }

    /**
     * @param array $row
     */
    protected function processCell($row)
    {
        $indexingMembers = array();
        // Member association
        $subSelect = $this->connection->query("SELECT * FROM Orga_Cell_Member WHERE idCell=" . $row['id']);
        /** @noinspection PhpAssignmentInConditionInspection */
        while ($rowAssociation = $subSelect->fetch()) {
            $indexingMembers[] = $this->getMember($rowAssociation['idMember']);
        }
        $subSelect->closeCursor();

        $cell = $this->getGranularity($row['idGranularity'])->getCellByMembers($indexingMembers);
        $cell->setRelevant((bool)$row['relevant']);

        echo "\t\t\t Cell : " . $cell->getLabel() . (($cell->getRelevant()) ? "" : " not") . " relevant\n";
     }

    /**
     * @param int $idCube
     */
    protected function migrateProjectAFConfig($idCube)
    {
        $select = $this->connection->query(
            "SELECT * FROM Inventory_AFGranularities JOIN Inventory_Project ON Inventory_AFGranularities.idProject = Inventory_Project.id WHERE idOrgaCube=$idCube"
        );
        /** @noinspection PhpAssignmentInConditionInspection */
        while ($row = $select->fetch()) {
            $this->processAFGranularities($row);
        }
        $select->closeCursor();
        $this->flush();
    }

    /**
     * @param array $row
     */
    protected function processAFGranularities($row)
    {
        $inputGranularity = $this->getGranularity($row['idAFInputOrgaGranularity']);
        $inputGranularity->setInputConfigGranularity($this->getGranularity($row['idAFConfigOrgaGranularity']));
        echo "\t\t Input Granularity : " . $inputGranularity->getInputConfigGranularity()->getLabel()
            . " configured by " . $inputGranularity->getInputConfigGranularity()->getLabel() . "\n";
    }

    /**
     * @param int $idCube
     */
    protected function migrateProjectGranularityDataProviders($idCube)
    {
        $select = $this->connection->query(
            "SELECT * FROM Orga_Granularity JOIN Inventory_GranularityDataProvider ON Orga_Granularity.id = Inventory_GranularityDataProvider.idOrgaGranularity WHERE idCube=$idCube"
        );
        /** @noinspection PhpAssignmentInConditionInspection */
        while ($row = $select->fetch()) {
            $this->processGranularityDataProvider($row);
            $this->flush();
        }
        $select->closeCursor();
    }

    /**
     * @param array $row
     */
    protected function processGranularityDataProvider($row)
    {
        $granularity = $this->getGranularity($row['idOrgaGranularity']);
        $granularity->setCellsWithOrgaTab((bool)$row['cellsWithOrgaTab']);
        $granularity->setCellsWithAFConfigTab((bool)$row['cellsWithAFConfigTab']);
        $granularity->setCellsGenerateDWCubes((bool)$row['cellsGenerateDWCubes']);
        $granularity->setCellsWithACL($row['cellsWithACL']);
        $granularity->setCellsWithSocialGenericActions((bool)$row['cellsWithSocialGenericActions']);
        $granularity->setCellsWithSocialContextActions((bool)$row['cellsWithSocialContextActions']);
        $granularity->setCellsWithInputDocs((bool)$row['cellsWithInputDocs']);
        $granularity->save();

        echo "\t\t GranularityDataProvider : " . $row['ref'] . " updated\n";
        echo "\t\t\t > " . (($granularity->getCellsWithOrgaTab()) ? "with" : "without") . " Orga tab\n";
        echo "\t\t\t > " . (($granularity->getCellsWithAFConfigTab()) ? "with" : "without")
            . " AF config tab\n";
        echo "\t\t\t > " . (($granularity->getCellsGenerateDWCubes()) ? "with" : "without")
            . " Cells generating DW Cubes\n";
        echo "\t\t\t > " . (($granularity->getCellsWithACL()) ? "with" : "without") . " ACL\n";
        echo "\t\t\t > " . (($granularity->getCellsWithSocialGenericActions()) ? "with" : "without")
            . " Social Generic actions\n";
        echo "\t\t\t > " . (($granularity->getCellsWithSocialContextActions()) ? "with" : "without")
            . " Social Context actions\n";
        echo "\t\t\t > " . (($granularity->getCellsWithInputDocs()) ? "with" : "without") . " Input docs\n";

        $project = $granularity->getProject();
        try {
            $setInventoryStatus = ($project->getGranularityForInventoryStatus() === $granularity);
        } catch (Core_Exception_UndefinedAttribute $e) {
            // Pas de granularité des inventaires.
        }
        $this->migrateGranularityCellDataProviders($row['idOrgaGranularity'], $setInventoryStatus);
    }

    /**
     * @param int $idGranularity
     * @param bool $setInventoryStatus
     */
    protected function migrateGranularityCellDataProviders($idGranularity, $setInventoryStatus = false)
    {
        $select = $this->connection->query(
            "SELECT * FROM Orga_Cell JOIN Inventory_CellDataProvider ON Orga_Cell.id = Inventory_CellDataProvider.idOrgaCell WHERE idGranularity=$idGranularity"
        );
        /** @noinspection PhpAssignmentInConditionInspection */
        while ($row = $select->fetch()) {
            $this->processCellDataProvider($row, $setInventoryStatus);
        }
        $select->closeCursor();
    }

    /**
     * @param array $row
     * @param bool $setInventoryStatus
     */
    protected function processCellDataProvider($row, $setInventoryStatus = false)
    {
        $indexingMembers = array();
        // Member association
        $subSelect = $this->connection->query("SELECT * FROM Orga_Cell_Member WHERE idCell=" . $row['idOrgaCell']);
        /** @noinspection PhpAssignmentInConditionInspection */
        while ($rowAssociation = $subSelect->fetch()) {
            $indexingMembers[] = $this->getMember($rowAssociation['idMember']);
        }
        $subSelect->closeCursor();

        $cell = Orga_Model_Cell::loadByGranularityAndListMembers(
            $this->getGranularity($row['idGranularity']),
            $indexingMembers
        );
        if ($setInventoryStatus === true) {
            $cell->setInventoryStatus(
                ($row['inventoryStatus'] === 'active') ? Orga_Model_Cell::STATUS_ACTIVE : Orga_Model_Cell::STATUS_NOTLAUNCHED
            );
        }
        if ($row['idAFInputSetPrimary'] != null) {
            $cell->setAFInputSetPrimary(AF_Model_InputSet_Primary::load($row['idAFInputSetPrimary']));
        }

        echo "\t\t\t Cell : " . $cell->getLabel() . " updated\n";
        echo "\t\t\t\t > Inventory status : " . $cell->getInventoryStatus() . "\n";

        // AF association
        $subSelect = $this->connection->query(
            "SELECT * FROM Inventory_CellsGroupDataProvider JOIN Inventory_AFGranularities ON idAFGranularities = Inventory_AFGranularities.id WHERE idContainerCellDataProvider=" . $row['id']
        );
        /** @noinspection PhpAssignmentInConditionInspection */
        if ($rowAssociation = $subSelect->fetch()) {
            $cellsGroup = $cell->getCellsGroupForInputGranularity(
                $this->getGranularity($rowAssociation['idAFInputOrgaGranularity'])
            );
            $cellsGroup->setAF(AF_Model_AF::load($rowAssociation['idAF']));
            $cellsGroup->save();

            echo "\t\t\t\t > CellsGroup for input granularity " . $cellsGroup->getInputGranularity()->getLabel() . " using AF : " . $cellsGroup->getAF()->getLabel() . "\n";
         }
        $subSelect->closeCursor();
    }

    /**
     * Migre les roles liés aux cellules
     */
    protected function migrateUserRoles()
    {
        echo "\t User Roles : \n";
        $cellDataProviders = array();

        $select = $this->connection->query("SELECT * FROM User_User");
        while ($rowUser = $select->fetch()) {
            $user = User_Model_User::loadByEmail($rowUser['email']);
            echo "\t\t " . $user->getName() . "\n";

            $subSelect = $this->connection->query(
                "SELECT * FROM User_Role JOIN User_RoleUser on User_RoleUser.idRole = User_Role.id WHERE User_RoleUser.idUser = " . $rowUser['id']
            );
            while ($rowAssociation = $subSelect->fetch()) {
                $refRole = $rowAssociation['code'];
                if (preg_match('#^(observer|admin)[0-9]+$#', $refRole, $matches)) {
                    $idOrgaCell = explode($matches[1], $matches[0])[1];
                    if (!isset($cellDataProviders[$idOrgaCell])) {
                        $cellDataProviders[$idOrgaCell] = $this->getCellDataProvider($idOrgaCell);
                    }
                    $role = User_Model_Role::loadByRef(
                        (($matches[1] === 'admin') ? 'cellDataProviderContributor_' : 'cellDataProviderObserver_') .
                        $cellDataProviders[$idOrgaCell]->getKey()['id']
                    );
                    $user->addRole($role);
                    echo "\t\t > " . $role->getName() . " for " . $cellDataProviders[$idOrgaCell]->getOrgaCell(
                        )->getLabel() . "\n";
                }
            }
            $subSelect->closeCursor();
        }
        $select->closeCursor();

        $this->flush();
    }

    /**
     * @param string $idOrgaCell
     * @return Inventory_Model_CellDataProvider
     */
    protected function getCellDataProvider($idOrgaCell)
    {
        $select = $this->connection->query("SELECT * FROM Orga_Cell WHERE id = " . $idOrgaCell);
        $rowCell = $select->fetch();
        $granularity = $this->getGranularity($rowCell['idGranularity']);

        $listMembers = array();
        $select = $this->connection->query("SELECT * FROM Orga_MemberCell WHERE idCell = " . $idOrgaCell);
        while ($rowMember = $select->fetch()) {
            $listMembers[] = $this->getMember($rowMember['idMember']);
        }
        $select->closeCursor();

        return Inventory_Model_CellDataProvider::loadByOrgaCell(
            Orga_Model_Cell::loadByGranularityAndListMembers(
                $granularity,
                $listMembers
            )
        );
    }

    /**
     *
     */
    protected function makeAdminProjectAdministrator()
    {
        $admin = User_Model_User::loadByEmail('contact@myc-sense.com');
        foreach ($this->mapIdProject as $idOrgaCube) {
            $project = Inventory_Model_Project::loadByOrgaCube(Orga_model_Cube::load($idOrgaCube));
            Inventory_Service_ACLManager::getInstance()->addProjectAdministrator($project, $admin);
            echo "\t\t L'utilisateur 'admin' administre désormais le projet " . $project->getKey()['id'] . "\n";
        }
        $this->flush();
    }


    /**
     * @param string $table
     * @param int $id
     * @return array
     */
    private function getById($table, $id)
    {
        $select = $this->connection->query("SELECT * FROM $table WHERE id=$id");
        $row = $select->fetch();
        $select->closeCursor();
        return $row;
    }

    /**
     * @param string $table
     * @return int
     */
    private function getCount($table)
    {
        $select = $this->connection->query("SELECT COUNT(*) as 'count' FROM $table");
        $row = $select->fetch();
        $select->closeCursor();
        return $row['count'];
    }

}
