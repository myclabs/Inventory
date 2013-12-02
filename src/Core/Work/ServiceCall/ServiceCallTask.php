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

    public function mergeEntities(EntityManager $entityManager)
    {
        foreach ($this->parameters as $i => $parameter) {
            // Gère les proxies.
            if ($parameter instanceof Proxy) {
                $realClassName = $entityManager->getClassMetadata(get_class($parameter))->getName();
                $this->parameters[$i] = $entityManager->find($realClassName, $parameter->getId());
                continue;
            }

            // Vérifie que c'est une entité Doctrine.
            if (is_object($parameter) && !$entityManager->getMetadataFactory()->isTransient(get_class($parameter))) {
                $this->parameters[$i] = $entityManager->find(get_class($parameter), $parameter->getId());
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
