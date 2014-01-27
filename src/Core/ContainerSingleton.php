<?php

namespace Core;

use DI\Container;
use Doctrine\ORM\EntityManager;

/**
 * Singleton access to the container.
 *
 * @deprecated Should not be used (in theory).
 */
class ContainerSingleton
{
    /**
     * @var Container
     */
    private static $container;

    /**
     * @return Container
     */
    public static function getContainer()
    {
        return self::$container;
    }

    /**
     * @return EntityManager
     */
    public static function getEntityManager()
    {
        return self::$container->get(EntityManager::class);
    }

    public static function setContainer(Container $container)
    {
        self::$container = $container;
    }
}
