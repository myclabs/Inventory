<?php

namespace Core\Work;

use Core\Work\WorkDispatcher;
use Core\Work\Task;
use Core\Work\Worker;

/**
 * Implémentation simpliste de WorkDispatcher: n'utilise pas de work queue, exécute directement les tâches
 *
 * @author matthieu.napoli
 */
class SimpleWorkDispatcher implements WorkDispatcher
{

    /**
     * Workers indexés par le nom de la tâche qu'ils traitent
     * @var Worker[]
     */
    private $workers = [];

    /**
     * {@inheritdoc}
     */
    public function run(Task $task)
    {
        $worker = $this->getWorker($task);

        return $worker->execute($task);
    }

    /**
     * {@inheritdoc}
     */
    public function runBackground(Task $task)
    {
        set_time_limit(0);
        $worker = $this->getWorker($task);

        $worker->execute($task);
    }

    /**
     * {@inheritdoc}
     */
    public function registerWorker(Worker $worker)
    {
        $this->workers[$worker->getTaskType()] = $worker;
    }

    /**
     * {@inheritdoc}
     */
    public function work()
    {
        // Rien à faire car les workers sont appelés directement par run()
    }

    /**
     * Retourne le worker enregistré pour une tâche donnée
     * @param Task $task
     * @return Worker|null
     */
    private function getWorker(Task $task)
    {
        $taskType = get_class($task);

        if (array_key_exists($taskType, $this->workers)) {
            return $this->workers[$taskType];
        }

        return null;
    }

}
