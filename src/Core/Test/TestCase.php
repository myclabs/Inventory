<?php

namespace Core\Test;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase;

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
     * @var ContainerInterface
     */
    private $container;

    /**
     * Set up
     */
    public function setUp()
    {
        $this->container = \Core\ContainerSingleton::getContainer();

        $this->container->injectOn($this);
    }

    /**
     * Get an entry from the container
     * @param string $name
     * @return mixed
     */
    protected function get($name)
    {
        return $this->container->get($name);
    }

    /**
     * À utiliser uniquement dans des méthodes statiques.
     *
     * @return EntityManager
     */
    protected static function getEntityManager()
    {
        return \Core\ContainerSingleton::getContainer()->get(EntityManager::class);
    }
}
