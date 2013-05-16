<?php
/**
 * @author     valentin.claras
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Script
 */
use Doctrine\ORM\EntityManager;

/**
 * Mise à jour d'un package
 *
 * @package    Core
 * @subpackage Script
 */
abstract class Core_Script_Update extends Core_Script_Action
{

    /**
     * Run the script for a specific environment.
     *
     * @param string $environment
     *
     * @void
     */
    protected function runEnvironment($environment)
    {
        // Récupération de la configuration de la connexion dans l'application.ini.
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', $environment);
        if (file_exists(APPLICATION_PATH . '/configs/shared.ini')) {
            $configShared = new Zend_Config_Ini(APPLICATION_PATH . '/configs/shared.ini', $environment, true);
            $configShared->merge($config);
            $config = $configShared;
        }

        $connectionSettings = $config->doctrine->default->connection;
        $connectionArray = array(
            'driver'        => $connectionSettings->driver,
            'user'          => $connectionSettings->user,
            'password'      => $connectionSettings->password,
            'dbname'        => $connectionSettings->dbname,
            'host'          => $connectionSettings->host,
            'port'          => $connectionSettings->port,
            'driverOptions' => array(
                1002 =>'SET NAMES utf8'
            ),
        );

        // Création de l'EntityManager depuis la configuration de doctrine.
        $doctrineConfig = Zend_Registry::get('doctrineConfiguration');
        $entityManager = Doctrine\ORM\EntityManager::create($connectionArray, $doctrineConfig);

        $this->updateDatabase($entityManager);
        echo "\t\tBase ".$connectionSettings->dbname." updated.".PHP_EOL;

        $this->generateProxies($entityManager);
        echo "\t\tProxies generated.".PHP_EOL;
    }

    /**
     * Update Doctrine
     * @param Doctrine\ORM\EntityManager $em
     */
    private function updateDatabase(EntityManager $em)
    {
        // Utilisation du SchemaTool afin de créer les tables pour l'ensemble du Model.
        $schemaTool = new Doctrine\ORM\Tools\SchemaTool($em);
        $schemaTool->updateSchema($em->getMetadataFactory()->getAllMetadata());
    }

    /**
     * Génère les proxies Doctrine
     * @param Doctrine\ORM\EntityManager $em
     */
    private function generateProxies(EntityManager $em)
    {
        $proxyFactory = $em->getProxyFactory();
        $allMetadata = $em->getMetadataFactory()->getAllMetadata();

        $proxyFactory->generateProxyClasses($allMetadata);
    }

}
