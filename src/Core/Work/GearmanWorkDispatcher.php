<?php

namespace Core\Work;

use Core\Work\WorkDispatcher;
use Core\Work\Notification\TaskNotifier;
use Core_Exception;
use Core_Locale;
use Core\Work\Task;
use Core\Work\TaskContext;
use Core\Work\Worker;
use DI\Annotation\Inject;
use Doctrine\ORM\EntityManager;
use Exception;
use GearmanClient;
use GearmanJob;
use GearmanWorker;
use Psr\Log\LoggerInterface;
use User_Model_User;
use Zend_Auth;

/**
 * Implémentation WorkDispatcher en utilisant Gearman
 *
 * @author matthieu.napoli
 */
class GearmanWorkDispatcher implements WorkDispatcher
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @Inject({"applicationName" = "application.name"})
     * @param string          $applicationName
     * @param EntityManager   $entityManager
     * @param TaskNotifier    $notifier
     * @param LoggerInterface $logger
     */
    public function __construct(
        $applicationName,
        EntityManager $entityManager,
        TaskNotifier $notifier,
        LoggerInterface $logger
    ) {
        $this->applicationName = $applicationName;
        $this->entityManager = $entityManager;
        $this->notifier = $notifier;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function run(Task $task)
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
    public function runBackground(Task $task)
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
    public function registerWorker(Worker $worker)
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

        $this->logger->info("Worker started");

        $worker = $this->getGearmanWorker();

        while (1) {
            $this->logger->info("Waiting for a job");

            // Exécute 1 job
            $worker->work();

            if ($worker->returnCode() != GEARMAN_SUCCESS) {
                break;
            }
        }
        $this->logger->error("Error while processing job: " . $worker->returnCode());
        $this->logger->error("Worker terminating");
        exit(1);
    }

    /**
     * Execute a job
     * @param Worker $worker
     * @param GearmanJob       $job
     * @return string Serialized job result
     */
    private function executeWorker(Worker $worker, GearmanJob $job)
    {
        $this->logger->info("Executing task " . $worker->getTaskType());

        /** @var Task $task */
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

        try {
            // Exécute la tâche
            $result = $worker->execute($task);

            // Flush et vide l'entity manager
            $this->entityManager->flush();
        } catch (Exception $e) {
            // Error notification
            if ($task->getTaskLabel() !== null && $task->getContext()->getUserId() !== null) {
                /** @var User_Model_User $user */
                $user = User_Model_User::load($task->getContext()->getUserId());

                $this->notifier->notifyTaskError($user, $task->getTaskLabel());
            }

            throw $e;
        }

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

        $this->logger->info("Task executed");

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
     * @param Task $task
     */
    private function saveContextInTask(Task $task)
    {
        $context = new TaskContext();

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
