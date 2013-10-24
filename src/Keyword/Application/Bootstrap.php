<?php
/**
 * @author matthieu.napoli
 * @author valentin.claras
 */

namespace Keyword\Application;

use Core_Package_Bootstrap;
use DI\Container;
use Doctrine\ORM\EntityManager;
use Keyword\Architecture\Repository\DoctrineAssociationRepository;
use Keyword\Domain\Association;
use Keyword\Domain\Keyword;
use Keyword\Domain\KeywordRepository;
use Keyword\Domain\Predicate;
use Keyword\Domain\PredicateRepository;

/**
 * Bootstrap
 */
class Bootstrap extends Core_Package_Bootstrap
{
    /**
     * Enregistrement des repository
     */
    protected function _initKeywordRepositories()
    {
        $this->container->set(
            KeywordRepository::class,
            function(Container $c) {
                return $c->get(EntityManager::class)->getRepository(Keyword::class);
            }
        );
        $this->container->set(
            PredicateRepository::class,
            function(Container $c) {
                return $c->get(EntityManager::class)->getRepository(Predicate::class);
            }
        );
        $this->container->set(
            DoctrineAssociationRepository::class,
            function(Container $c) {
                return $c->get(EntityManager::class)->getRepository(Association::class);
            }
        );
    }
}
