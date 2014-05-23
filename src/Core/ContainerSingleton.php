<?php

namespace Core;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

/**
 * Singleton access to the container.
 *
 * @deprecated Should not be used (in theory).
 */
class ContainerSingleton
{
    /**
     * @var ContainerInterface
     */
    private static $container;

    /**
     * @return ContainerInterface
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

    public static function setContainer(ContainerInterface $container)
    {
        self::$container = $container;
    }
}
