<?php

namespace Core\Work\ServiceCall;

use Core\Work\BaseTaskInterface;
use Core\Work\BaseTaskTrait;
use Core\Work\SerializedEntity;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use MyCLabs\Work\Task\ServiceCall;

/**
 * Représente l'appel d'une méthode d'un service.
 *
 * @author matthieu.napoli
 */
class ServiceCallTask extends ServiceCall implements BaseTaskInterface, \Serializable
{
    use BaseTaskTrait;

    /**
     * @var EntityManager
     */
    private $entityManager;

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

    public function reloadEntities()
    {
        $unserializeEntities = function (array $data) use (&$unserializeEntities) {
            $newData = [];
            foreach ($data as $parameter => $value) {
                if ($value instanceof SerializedEntity) {
                    $newData[$parameter] = $this->entityManager->find($value->class, $value->id);
                } elseif (is_array($value)) {
                    $newData[$parameter] = $unserializeEntities($value);
                } else {
                    $newData[$parameter] = $value;
                }
            }
            return $newData;
        };

        $this->parameters = $unserializeEntities($this->parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return "$this->serviceName::$this->methodName";
    }

    /**
     * Gestion spéciale des entités dans le tableau des paramètres.
     *
     * @return string
     */
    public function serialize()
    {
        $data = get_object_vars($this);
        unset($data['entityManager']);

        $metadataFactory = $this->entityManager->getMetadataFactory();

        $serializeEntities = function (array $parameters) use (&$serializeEntities, $metadataFactory) {
            $newParameters = [];
            foreach ($parameters as $i => $parameter) {
                if (is_object($parameter) && !$metadataFactory->isTransient(ClassUtils::getClass($parameter))) {
                    // Si c'est une entité Doctrine
                    $newParameters[$i] = new SerializedEntity(ClassUtils::getClass($parameter), $parameter->getId());
                } elseif (is_array($parameter)) {
                    $newParameters[$i] = $serializeEntities($parameter);
                } else {
                    $newParameters[$i] = $parameter;
                }
            }
            return $newParameters;
        };

        $data['parameters'] = $serializeEntities($this->parameters);

        return serialize($data);
    }

    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        foreach ($data as $parameter => $value) {
            $this->$parameter = $value;
        }
    }

    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }
}
