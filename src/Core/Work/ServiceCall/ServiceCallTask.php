<?php

namespace Core\Work\ServiceCall;

use Core\Work\BaseTaskInterface;
use Core\Work\BaseTaskTrait;
use Doctrine\Common\Persistence\Proxy;
use Doctrine\ORM\EntityManager;
use MyCLabs\Work\Task\ServiceCall;

/**
 * Représente l'appel d'une méthode d'un service.
 *
 * @author matthieu.napoli
 */
class ServiceCallTask extends ServiceCall implements BaseTaskInterface
{
    use BaseTaskTrait;

    /**
     * @param string $serviceName Name of the service class
     * @param string $methodName  Name of the method to call
     * @param array  $parameters  Parameters for the method call, must be serializable
     * @param string $taskLabel   Nom de la tache pour le système de notification
     */
    public function __construct($serviceName, $methodName, array $parameters = [], $taskLabel = null)
    {
        parent::__construct($serviceName, $methodName, $parameters);

        $this->setTaskLabel($taskLabel);
    }

    public function reloadEntities(EntityManager $entityManager)
    {
        $this->reloadArray($this->parameters, $entityManager);
    }

    protected function reloadArray(array &$entitiesArray, EntityManager $entityManager)
    {
        foreach ($entitiesArray as $i => $entity) {
            // Gère les proxies
            if ($entity instanceof Proxy) {
                $realClassName = $entityManager->getClassMetadata(get_class($entity))->getName();
                $this->parameters[$i] = $entityManager->find($realClassName, $entity->getId());
                continue;
            }

            // Vérifie que c'est une entité Doctrine
            if (is_object($entity) && !$entityManager->getMetadataFactory()->isTransient(get_class($entity))) {
                $this->parameters[$i] = $entityManager->find(get_class($entity), $entity->getId());
            }

            if (is_array($entity)) {
                $this->reloadArray($entity, $entityManager);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return "$this->serviceName::$this->methodName";
    }
}
