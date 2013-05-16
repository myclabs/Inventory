<?php
/**
 * @author valentin.claras
 * @package Unit
 */

use Doctrine\DBAL\Types\Type;

/**
 * Bootstrap
 * @author valentin.claras
 * @package Unit
 */
class Unit_Bootstrap extends Core_Package_Bootstrap
{

    /**
     * Initialise le mapping des types en BDD
     */
    protected function _initUnitTypeMapping()
    {
        Type::addType(Unit_TypeMapping_UnitAPI::TYPE_NAME, 'Unit_TypeMapping_UnitAPI');
    }

    /**
     * Initialise la connexion avec la base de données des unités.
     */
    protected function _initUnitDB()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');

        // Récupération de la configuration de la connexion dans l'application.ini
        $doctrineConnectionSettings = Zend_Registry::get('configuration')->doctrine;
        // Si présents on utilise les paramètres spécifiques à Unit, sinon l'EntityManager par défaut.
        if (isset($doctrineConnectionSettings->unit)) {
            $unitConnectionSettings = $doctrineConnectionSettings->unit->connection;
            $unitConnectionArray = array(
                'driver'    => $unitConnectionSettings->driver,
                'user'      => $unitConnectionSettings->user,
                'password'  => $unitConnectionSettings->password,
                'dbname'    => $unitConnectionSettings->dbname,
                'host'      => $unitConnectionSettings->host,
                'port'      => $unitConnectionSettings->port,
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

}
