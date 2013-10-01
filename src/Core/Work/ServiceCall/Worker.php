<?php

use DI\Container;
use Psr\Log\LoggerInterface;

/**
 * Exécute l'appel d'une méthode d'un service
 *
 * @author  matthieu.napoli
 */
class Core_Work_ServiceCall_Worker extends Core_Work_Worker
{

    /**
     * @var Container
     */
    private $container;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Container       $container
     * @param LoggerInterface $logger
     */
    public function __construct(Container $container, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->logger = $logger;
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

        $this->logger->debug("Calling $serviceName::$methodName");

        // Appelle la méthode du service
        $return = call_user_func_array(array($service, $methodName), $parameters);

        return $return;
    }

}
