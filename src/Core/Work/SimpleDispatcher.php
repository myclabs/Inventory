<?php
/**
 * @author  matthieu.napoli
 * @package Core
 */

/**
 * Implémentation simpliste de WorkDispatcher: n'utilise pas de work queue, exécute directement les tâches
 *
 * @package Core
 */
class Core_Work_SimpleDispatcher implements Core_Work_Dispatcher
{

    /**
     * Workers indexés par le nom de la tâche qu'ils traitent
     * @var Core_Work_Worker[]
     */
    private $workers = [];

    /**
     * {@inheritdoc}
     */
    public function run(Core_Work_Task $task)
    {
        $worker = $this->getWorker($task);

        return $worker->execute($task);
    }

    /**
     * {@inheritdoc}
     */
    public function runBackground(Core_Work_Task $task)
    {
        set_time_limit(0);
        $worker = $this->getWorker($task);

        $worker->execute($task);
    }

    /**
     * {@inheritdoc}
     */
    public function registerWorker(Core_Work_Worker $worker)
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
     * @param Core_Work_Task $task
     * @return Core_Work_Worker|null
     */
    private function getWorker(Core_Work_Task $task)
    {
        $taskType = get_class($task);

        if (array_key_exists($taskType, $this->workers)) {
            return $this->workers[$taskType];
        }

        return null;
    }

}
