<?php
/**
 * @author matthieu.napoli
 * @package Core
 */

/**
 * Classe de test de base
 * @package Core
 */
abstract class Core_Test_TestCase extends PHPUnit_Framework_TestCase
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * Set up
     */
    public function setUp()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        $this->entityManager = $entityManagers['default'];
    }

    /**
     * Set up
     */
    public function tearDown()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        /** @var $entityManager \Doctrine\ORM\EntityManager */
        $entityManager = $entityManagers['default'];

        // Si l'entity manager est fermé (à cause d'une exception)
        if (!$entityManager->isOpen()) {
            // Réinitialise l'entity manager
            /** @var $bootstrap Core_Bootstrap */
            $bootstrap = Zend_Registry::get('bootstrap');
            $newEntityManager = $bootstrap->createDefaultEntityManager();

            $entityManagers['default'] = $newEntityManager;
            Zend_Registry::set('EntityManagers', $entityManagers);
        }

        $this->entityManager = $entityManagers['default'];
    }

}
