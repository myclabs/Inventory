<?php
use Doctrine\ORM\EntityManager;

/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Script
 */

/**
 * Class for Populate action.
 *
 * @package    Core
 * @subpackage Script
 */
abstract class Core_Script_Populate extends Core_Script_Action
{
    /**
     * List all environments where the action can be made.
     *
     * @var array
     */
    protected $acceptedEnvironments = array('developpement', 'test', 'production', 'testsunitaires');

    /**
     * Run the action for each environment.
     *
     * @param string $environment
     *
     * @return void
     */
    protected function runEnvironment($environment)
    {
        if (!in_array($environment, $this->acceptedEnvironments)) {
            return;
        }

        $container = \Core\ContainerSingleton::getContainer();
        /** @var $bootstrap Core_Bootstrap */
        $bootstrap = Zend_Registry::get('bootstrap');

        // Récupération de la configuration de la connexion dans l'application.ini.
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', $environment);
        if (file_exists(APPLICATION_PATH . '/configs/shared.ini')) {
            $configShared = new Zend_Config_Ini(APPLICATION_PATH . '/configs/shared.ini', $environment, true);
            $configShared->merge($config);
            $config = $configShared;
        }

        $connectionSettings = $config->doctrine->default->connection;
        $entityManager = $bootstrap->createDefaultEntityManager($connectionSettings);

        $container->set(EntityManager::class, $entityManager);

        // Lancement du populate.
        $this->populateEnvironment($environment);

        $entityManager->close();
        $container->set(EntityManager::class, null);
    }

    /**
     * Populate a specific environment.
     *
     * @param string $environment
     *
     * @void
     */
    protected function populateEnvironment($environment)
    {
        echo "\tNothing done for $environment.".PHP_EOL;
    }

}
