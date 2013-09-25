<?php

namespace Core\Work\ServiceCall;

use Core\Work\BaseTaskInterface;
use Core\Work\BaseTaskTrait;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use MyCLabs\Work\Task\ServiceCall;
use Core_Model_Entity;

/**
 * Représente l'appel d'une méthode d'un service.
 *
 * @author matthieu.napoli
 */
class ServiceCallTask extends ServiceCall implements BaseTaskInterface
{
    use BaseTaskTrait;

    public function detachEntities(EntityManager $entityManager)
    {
        foreach ($this->parameters as $parameter) {
            // Vérifie que c'est une entité Doctrine
            if (! $entityManager->getMetadataFactory()->isTransient(get_class($parameter))) {
                $entityManager->detach($parameter);
            }
        }
    }

    public function mergeEntities(EntityManager $entityManager)
    {
        foreach ($this->parameters as $parameter) {
            // Vérifie que c'est une entité Doctrine
            if (! $entityManager->getMetadataFactory()->isTransient($parameter)) {
                $entityManager->merge($parameter);
            }
        }
    }
}
