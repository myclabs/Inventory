<?php

use Core\Work\Notification\TaskNotifier;
use DI\Annotation\Inject;
use Doctrine\ORM\EntityManager;

/**
 * Implémentation WorkDispatcher en utilisant Gearman
 *
 * @author  matthieu.napoli
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
     * @var string
     */
    private $applicationName;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var TaskNotifier
     */
    private $notifier;

    /**
     * @Inject({"applicationName" = "application.name"})
     * @param string        $applicationName
     * @param EntityManager $entityManager
     * @param TaskNotifier  $notifier
     */
    public function __construct($applicationName, EntityManager $entityManager, TaskNotifier $notifier)
    {
        $this->applicationName = $applicationName;
        $this->entityManager = $entityManager;
        $this->notifier = $notifier;
    }

    /**
     * {@inheritdoc}
     */
    public function run(Core_Work_Task $task)
    {
        $this->saveContextInTask($task);

        $taskType = $this->prefixTaskType(get_class($task));
        $workload = serialize($task);

        $return = $this->getGearmanClient()->doNormal($taskType, $workload);

        return unserialize($return);
    }

    /**
     * {@inheritdoc}
     */
    public function runBackground(Core_Work_Task $task)
    {
        $this->saveContextInTask($task);

        $taskType = $this->prefixTaskType(get_class($task));
        $workload = serialize($task);

        $this->getGearmanClient()->doBackground($taskType, $workload);

        if ($this->getGearmanClient()->returnCode() != GEARMAN_SUCCESS) {
            throw new Core_Exception("Gearman error: " . $this->getGearmanClient()->returnCode());
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
        $this->entityManager->getConnection()->close();

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
     * @return string Serialized job result
     */
    private function executeWorker(Core_Work_Worker $worker, GearmanJob $job)
    {
        Core_Error_Log::getInstance()->info("Executing task " . $worker->getTaskType());

        /** @var Core_Work_Task $task */
        $task = unserialize($job->workload());

        // Change la locale
        if ($task->getContext() && $task->getContext()->getUserLocale()) {
            $oldDefaultLocale = Core_Locale::loadDefault();
            Core_Locale::setDefault($task->getContext()->getUserLocale());
        } else {
            $oldDefaultLocale = null;
        }

        // Connexion BDD et transaction
        $this->entityManager->getConnection()->connect();
        $this->entityManager->beginTransaction();

        // Exécute la tâche
        $result = $worker->execute($task);

        // Flush et vide l'entity manager
        $this->entityManager->flush();
        $this->entityManager->commit();
        $this->entityManager->clear();

        // Notification
        if ($task->getTaskLabel() !== null && $task->getContext()->getUserId() !== null) {
            /** @var User_Model_User $user */
            $user = User_Model_User::load($task->getContext()->getUserId());

            $this->notifier->notifyTaskFinished($user, $task->getTaskLabel());
        }

        $this->entityManager->getConnection()->close();

        // Rétablit la locale
        if ($oldDefaultLocale) {
            Core_Locale::setDefault($oldDefaultLocale);
        }

        Core_Error_Log::getInstance()->info("Task executed");

        // Retourne le résultat sérialisé
        return serialize($result);
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
        return $this->applicationName . '::' . $taskType;
    }

    /**
     * @param Core_Work_Task $task
     */
    private function saveContextInTask(Core_Work_Task $task)
    {
        $context = new Core_Work_TaskContext();

        // Locale
        $context->setUserLocale(Core_Locale::loadDefault());

        // User
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $context->setUserId($auth->getIdentity());
        }

        $task->setContext($context);
    }

}
