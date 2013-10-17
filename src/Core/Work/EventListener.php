<?php

namespace Core\Work;

use Core\Work\Notification\TaskNotifier;
use Core\Work\ServiceCall\ServiceCallTask;
use Core_Locale;
use Doctrine\ORM\EntityManager;
use Exception;
use MyCLabs\Work\Task\Task;
use Psr\Log\LoggerInterface;
use User_Model_User;
use Zend_Auth;

/**
 * @author matthieu.napoli
 */
class EventListener extends \MyCLabs\Work\EventListener
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var TaskNotifier
     */
    private $notifier;

    public function __construct(LoggerInterface $logger, EntityManager $entityManager, TaskNotifier $notifier)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->notifier = $notifier;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeTaskDispatched(Task $task)
    {
        // Sauvegarde le contexte
        if ($task instanceof BaseTaskInterface) {
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

    /**
     * {@inheritdoc}
     */
    public function beforeTaskSerialization(Task $task)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function afterTaskUnserialization(Task $task)
    {
        // Traitement spécial pour les entités Doctrine
        if ($task instanceof ServiceCallTask) {
            $task->mergeEntities($this->entityManager);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function beforeTaskExecution(Task $task)
    {
        $this->logger->info("Executing task {task}", ['task' => (string) $task]);

        if ($task instanceof BaseTaskInterface) {
            // Change la locale
            if ($task->getContext() && $task->getContext()->getUserLocale()) {
                Core_Locale::setDefault($task->getContext()->getUserLocale());
            }
        }

        // Connexion BDD et transaction
        $this->entityManager->getConnection()->connect();
        $this->entityManager->beginTransaction();
    }

    /**
     * {@inheritdoc}
     */
    public function beforeTaskFinished(Task $task)
    {
        // Commit transaction
        $this->entityManager->flush();
        $this->entityManager->commit();
    }

    /**
     * {@inheritdoc}
     */
    public function onTaskError(Task $task, Exception $e, $dispatcherNotified)
    {
        $this->logger->error("Error while executing task {task}", ['exception' => $e, 'task' => (string) $task]);

        if ($task instanceof BaseTaskInterface) {
            // Error notification
            if (!$dispatcherNotified && $task->getTaskLabel() !== null && $task->getContext()->getUserId() !== null) {
                /** @var User_Model_User $user */
                $user = User_Model_User::load($task->getContext()->getUserId());

                $this->notifier->notifyTaskError($user, $task->getTaskLabel());
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onTaskSuccess(Task $task, $dispatcherNotified)
    {
        if ($task instanceof BaseTaskInterface) {
            // Notification
            if (!$dispatcherNotified && $task->getTaskLabel() !== null && $task->getContext()->getUserId() !== null) {
                /** @var User_Model_User $user */
                $user = User_Model_User::load($task->getContext()->getUserId());

                $this->logger->info(
                    "Task {task} executed, notifying {user} by mail ",
                    ['task' => (string) $task, 'user' => $user->getEmail()]
                );

                $this->notifier->notifyTaskFinished($user, $task->getTaskLabel());

                return;
            }
        }

        $this->logger->info("Task {task} executed", ['task' => (string) $task]);
    }
}
