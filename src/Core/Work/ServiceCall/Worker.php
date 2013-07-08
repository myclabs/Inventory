<?php
/**
 * @author  matthieu.napoli
 * @package Core
 */

use DI\Container;

/**
 * Exécute l'appel d'une méthode d'un service
 *
 * @package Core
 */
class Core_Work_ServiceCall_Worker extends Core_Work_Worker
{

    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getTaskType()
    {
        return 'Core_Work_ServiceCall_Task';
    }

    /**
     * {@inheritdoc}
     * @param Core_Work_ServiceCall_Task $task
     */
    public function execute(Core_Work_Task $task)
    {
        $serviceName = $task->getServiceName();
        $methodName = $task->getMethodName();
        $parameters = $task->getParameters();

        // Récupère le service depuis le container
        $service = $this->container->get($serviceName);

        Core_Error_Log::getInstance()->debug("Calling $serviceName::$methodName");

        // Appelle la méthode du service
        $return = call_user_func_array(array($service, $methodName), $parameters);

        return $return;
    }

}
