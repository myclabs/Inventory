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
     * @Inject
     * @var \Doctrine\ORM\EntityManager
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
}
