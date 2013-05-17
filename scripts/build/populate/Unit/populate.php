<?php
/**
 * @package Unit
 */
require_once 'populatePhysicalQuantities.php';
require_once 'populateStandardUnit.php';
require_once 'populateUnitSystem.php';
require_once 'populateDiscreteUnit.php';
require_once 'populateExtendedUnit.php';
require_once 'populateExtension.php';

/**
 * @package Unit
 */
class Unit_Populate extends Core_Script_Populate
{
    /**
     * Populate a specific environment.
     *
     * @param string $environment
     *
     * @void
     */
    public function populateEnvironment($environment)
    {
        $this->initUnitEntityManager($environment);

        echo PHP_EOL.'\-- Script de création des Units pour '.$environment.' -->'.PHP_EOL;

        $unitSystems = new Unit_Script_Populate_UnitSystem();
        $unitSystems->run();
        echo PHP_EOL."\t\t".' ..UnitSystems created !'.PHP_EOL;

        $quantitieUnits = new Unit_Script_Populate_PhysicalQuantities();
        $quantitieUnits->run();
        echo PHP_EOL."\t\t".' ..PhysicalQuantities created !'.PHP_EOL;

        $standardUnits = new Unit_Script_Populate_StandardUnit();
        $standardUnits->run();
        echo PHP_EOL."\t\t".' ..StandardUnits created !'.PHP_EOL;

        $quantitieUnits = new Unit_Script_Populate_PhysicalQuantities();
        $quantitieUnits->update();
        echo PHP_EOL."\t\t".' ..PhysicalQuantities updated with referenceUnits !'.PHP_EOL;

        $discreteUnits = new Unit_Script_Populate_DiscreteUnit();
        $discreteUnits->run();
        echo PHP_EOL."\t\t".' ..DiscreteUnits created !'.PHP_EOL;

        $extensions = new Unit_Script_Populate_Extension();
        $extensions->run();
        echo PHP_EOL."\t\t".' ..UnitExtensions created !'.PHP_EOL;

        $extendedUnits = new Unit_Script_Populate_extendedUnit();
        $extendedUnits->run();
        echo PHP_EOL."\t\t".' ..ExtendedUnits created !'.PHP_EOL;

        echo PHP_EOL."\t".'--> Script de création des Units --\\'.PHP_EOL;

        $this->resetUnitEntityManager($environment);
    }

    /**
     * Initialise la connection et l'EntityManager de Unit.
     * @param string $environment
     */
    protected function initUnitEntityManager($environment)
    {
        $entityManagers = Zend_Registry::get('EntityManagers');

        // Récupération de la configuration de la connexion dans l'application.ini
        $config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/application.ini', $environment);
        $doctrineConnectionSettings = $config->doctrine;
        // Si présents on utilise les paramètres spécifiques à Unit, sinon l'EntityManager par défaut.
        if (isset($doctrineConnectionSettings->unit)) {
            $unitConnectionSettings = $doctrineConnectionSettings->unit->connection;
            $unitConnectionArray = array(
                    'driver'    => $unitConnectionSettings->driver,
                    'user'      => $unitConnectionSettings->user,
                    'password'  => $unitConnectionSettings->password,
                    'dbname'    => $unitConnectionSettings->dbname,
                    'host'      => $unitConnectionSettings->host,
                    'driverOptions' => array(
                            1002 =>'SET NAMES utf8'
                        ),
            );

            // Création de l'EntityManager depuis la configuration de doctrine.
            /* @var $doctrineConfig Doctrine\ORM\Configuration */
            $doctrineConfig = Zend_Registry::get('doctrineConfiguration');
            // Création de l'EntityManager spécifique.
            $unitEntityManager = Doctrine\ORM\EntityManager::create($unitConnectionArray, $doctrineConfig);
        } else {
            // Utilisation de l'EntityManager par défaut.
            $unitEntityManager = $entityManagers['default'];
        }
        // Ajout de l'EntityManager au tableau.
        $entityManagers['unit'] = $unitEntityManager;
        Zend_Registry::set('EntityManagers', $entityManagers);

        // Désignation des PoolName spécifique à UI.
        //  Les Objets du sous-package Unit utilisent le PoolsNames de Unit_Model_Unit.
        Unit_Model_Unit::setActivePoolName('unit');
        Unit_Model_PhysicalQuantity::setActivePoolName('unit');
    }

    /**
     * Reset la connection et l'EntityManager de Unit.
     */
    protected function resetUnitEntityManager()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['unit']->close();
        unset($entityManagers['unit']);
        Zend_Registry::set('EntityManagers', $entityManagers);
    }
}