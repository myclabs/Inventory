<?php

namespace Core\Test;

use Doctrine\ORM\EntityManager;
use PHPUnit_Framework_TestCase;
use Zend_Registry;

/**
 * Classe de test de base.
 *
 * @author matthieu.napoli
 */
abstract class TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @Inject
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Set up
     */
    public function setUp()
    {
        /** @var $container \DI\Container */
        $container = Zend_Registry::get('container');
        $container->injectOn($this);
    }

    /**
     * Get an entry from the container
     * @param string $name
     * @return mixed
     */
    protected function get($name)
    {
        /** @var $container \DI\Container */
        $container = Zend_Registry::get('container');
        return $container->get($name);
    }

    /**
     * À utiliser uniquement dans des méthodes statiques.
     *
     * Évite de passer par Zend_Registry.
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected static function getEntityManager()
    {
        /** @var $container \DI\Container */
        $container = Zend_Registry::get('container');
        return $container->get(EntityManager::class);
    }
}
