<?php

namespace Core\Work\EventListener;

use Exception;
use MyCLabs\Work\Task\Task;
use MyCLabs\Work\Worker\Event\WorkerEventListener;
use Psr\Log\LoggerInterface;

/**
 * Event listener pour le SimpleWorker
 *
 * @author matthieu.napoli
 */
class InMemoryWorkerEventListener implements WorkerEventListener
{

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeTaskExecution(Task $task)
    {
        set_time_limit(0);
        $this->logger->info("Executing task {task}", ['task' => (string) $task]);
    }

    /**
     * {@inheritdoc}
     */
    public function afterTaskUnserialization(Task $task)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function beforeTaskFinished(Task $task)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function onTaskSuccess(Task $task, $dispatcherNotified)
    {
        $this->logger->info("Task {task} executed", ['task' => (string) $task]);
    }

    /**
     * {@inheritdoc}
     */
    public function onTaskError(Task $task, Exception $e, $dispatcherNotified)
    {
        $this->logger->error("Error while executing task {task}", ['exception' => $e, 'task' => (string) $task]);
    }
}
