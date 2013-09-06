<?php

/**
 * Représente l'appel d'une méthode d'un service
 *
 * @author  matthieu.napoli
 */
class Core_Work_ServiceCall_Task extends Core_Work_Task
{

    /**
     * @var string
     */
    private $serviceName;

    /**
     * @var string
     */
    private $methodName;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @param string      $serviceName Nom de la classe du service
     * @param string      $methodName  Nom de la méthode à appeler sur le service
     * @param array       $parameters  Paramètres de l'appel à la méthode
     * @param null|string $taskLabel
     */
    public function __construct($serviceName, $methodName, array $parameters = array(), $taskLabel = null)
    {
        $this->serviceName = $serviceName;
        $this->methodName = $methodName;
        $this->parameters = $parameters;
        if ($taskLabel) {
            $this->setTaskLabel($taskLabel);
        }
    }

    /**
     * @return string
     */
    public function getServiceName()
    {
        return $this->serviceName;
    }

    /**
     * @return string
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Déserialisation
     */
    public function __wakeup()
    {
        // Après sérialisation, recharge les entités
        foreach ($this->parameters as $index => $parameter) {
            if ($parameter instanceof Core_Model_Entity) {
                $this->parameters[$index] = $parameter::load($parameter->getKey());
            }
        }
    }

}
