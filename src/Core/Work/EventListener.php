<?php

namespace Core\Work;

use Doctrine\ORM\EntityManager;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * @author matthieu.napoli
 */
class EventListener extends \MyCLabs\Work\Worker\EventListener
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(LoggerInterface $logger, EntityManager $entityManager)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeTaskExecution(Task $task)
    {
        $this->logger->info("Executing task {task}", ['task' => (string) $task]);

        // Change la locale
        // TODO

        // Connexion BDD et transaction
        $this->entityManager->getConnection()->connect();
        $this->entityManager->beginTransaction();
    }

    /**
     * {@inheritdoc}
     */
    public function onTaskSuccess(Task $task)
    {
        $this->entityManager->flush();

        // Commit transaction
        $this->entityManager->commit();

        $this->logger->info("Task {task} executed", ['task' => (string) $task]);
    }

    /**
     * {@inheritdoc}
     */
    public function onTaskException(Task $task, Exception $e)
    {
        $this->logger->error("Error while executing task {task}", ['exception' => $e, 'task' => (string) $task]);
    }
}
