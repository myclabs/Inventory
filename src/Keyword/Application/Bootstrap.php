<?php
/**
 * @author matthieu.napoli
 * @author valentin.claras
 */

namespace Keyword\Application;

use Core_Package_Bootstrap;
use DI\Container;

/**
 * Bootstrap
 */
class Bootstrap extends Core_Package_Bootstrap
{
    /**
     * Enregistrement des repository
     */
    protected function _initRepositories()
    {
        $this->container->set(
            'Keyword\Domain\KeywordRepository',
            function(Container $c) {
                return $c->get('Doctrine\ORM\EntityManager')->getRepository('Keyword\Domain\Keyword');
            }
        );
        $this->container->set(
            'Keyword\Domain\PredicateRepository',
            function(Container $c) {
                return $c->get('Doctrine\ORM\EntityManager')->getRepository('Keyword\Domain\Predicate');
            }
        );
    }
}
