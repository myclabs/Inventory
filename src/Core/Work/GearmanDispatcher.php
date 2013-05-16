<?php
/**
 * @author  matthieu.napoli
 * @package Core
 */
use Doctrine\ORM\EntityManager;

/**
 * Implémentation WorkDispatcher en utilisant Gearman
 *
 * @package Core
 */
class Core_Work_GearmanDispatcher implements Core_Work_Dispatcher
{

    /**
     * @var GearmanClient|null
     */
    private $client;

    /**
     * @var GearmanWorker|null
     */
    private $worker;


    /**
     * {@inheritdoc}
     */
    public function run(Core_Work_Task $task)
    {
        $taskType = $this->prefixTaskType(get_class($task));
        $workload = serialize($task);

        return $this->getGearmanClient()->doNormal($taskType, $workload);
    }

    /**
     * {@inheritdoc}
     */
    public function runBackground(Core_Work_Task $task)
    {
        $taskType = $this->prefixTaskType(get_class($task));
        $workload = serialize($task);

        $this->getGearmanClient()->doBackground($taskType, $workload);

        if ($this->getGearmanClient()->returnCode() != GEARMAN_SUCCESS) {
            throw new Core_Exception("Gearman error: " . $this->client->returnCode());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function registerWorker(Core_Work_Worker $worker)
    {
        $taskType = $this->prefixTaskType($worker->getTaskType());

        $this->getGearmanWorker()->addFunction(
            $taskType,
            function (GearmanJob $job) use ($worker) {
                return $this->executeWorker($worker, $job);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function work()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        /** @var EntityManager $entityManager */
        $entityManager = $entityManagers['default'];
        $entityManager->getConnection()->close();

        Core_Error_Log::getInstance()->info("Worker started");

        $worker = $this->getGearmanWorker();

        while (1) {
            Core_Error_Log::getInstance()->info("Waiting for a job");

            // Exécute 1 job
            $worker->work();

            if ($worker->returnCode() != GEARMAN_SUCCESS) {
                break;
            }
        }
        Core_Error_Log::getInstance()->error("Error while processing job: " . $worker->returnCode());
        Core_Error_Log::getInstance()->error("Worker terminating");
        exit(1);
    }

    /**
     * Execute a job
     * @param Core_Work_Worker $worker
     * @param GearmanJob       $job
     * @return mixed Job result
     */
    private function executeWorker(Core_Work_Worker $worker, GearmanJob $job)
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        /** @var EntityManager $entityManager */
        $entityManager = $entityManagers['default'];

        Core_Error_Log::getInstance()->info("Executing task " . $worker->getTaskType());

        // Connexion BDD et transaction
        $entityManager->getConnection()->connect();
        $entityManager->beginTransaction();

        $task = unserialize($job->workload());
        $result = $worker->execute($task);

        // Flush et vide l'entity manager
        $entityManager->flush();
        $entityManager->commit();
        $entityManager->clear();
        $entityManager->getConnection()->close();

        Core_Error_Log::getInstance()->info("Task executed");

        return $result;
    }

    /**
     * @return GearmanClient
     */
    private function getGearmanClient()
    {
        if (!$this->client) {
            $this->client = new GearmanClient();
            $this->client->addServer();
            $this->client->setTimeout(2000);
        }
        return $this->client;
    }

    /**
     * @return GearmanWorker
     */
    private function getGearmanWorker()
    {
        if (!$this->worker) {
            $this->worker = new GearmanWorker();
            $this->worker->addServer();
        }
        return $this->worker;
    }

    /**
     * @param string $taskType
     * @return string
     */
    private function prefixTaskType($taskType)
    {
        return Zend_Registry::get('applicationName') . '::' . $taskType;
    }

}
