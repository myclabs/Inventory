<?php

namespace Core\Work\EventListener;

use Exception;
use MyCLabs\Work\Task\Task;
use MyCLabs\Work\Worker\Event\WorkerEventListener;

/**
 * Event listener pour le SimpleWorker
 *
 * @author matthieu.napoli
 */
class InMemoryWorkerEventListener implements WorkerEventListener
{
    /**
     * {@inheritdoc}
     */
    public function beforeTaskExecution(Task $task)
    {
        set_time_limit(0);
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
    }

    /**
     * {@inheritdoc}
     */
    public function onTaskError(Task $task, Exception $e, $dispatcherNotified)
    {
    }
}
