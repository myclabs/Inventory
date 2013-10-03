<?php

/**
 * Classe de test de base
 * @author matthieu.napoli
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
}
