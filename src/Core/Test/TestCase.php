<?php

namespace Core\Test;

use Doctrine\ORM\EntityManager;
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
     * Set up
     */
    public function setUp()
    {
        \Core\ContainerSingleton::getContainer()->injectOn($this);
    }

    /**
     * Get an entry from the container
     * @param string $name
     * @return mixed
     */
    protected function get($name)
    {
        return \Core\ContainerSingleton::getContainer()->get($name);
    }

    /**
     * À utiliser uniquement dans des méthodes statiques.
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected static function getEntityManager()
    {
        return \Core\ContainerSingleton::getContainer()->get(EntityManager::class);
    }
}
