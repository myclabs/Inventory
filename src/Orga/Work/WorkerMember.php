<?php
/**
 * @author  valentin.claras
 * @author  matthieu.napoli
 * @package Core
 */
use Core\Work\ServiceCall\ServiceCallTask;
use Core\Work\Worker;
use Core\Work\Task;

/**
 * Exécute l'appel d'une méthode d'une entité.
 *
 * @package Core
 */
class Orga_Work_WorkerMember extends Worker
{

    /**
     * {@inheritdoc}
     */
    public function getTaskType()
    {
        return 'Orga_Work_Task_AddMember';
    }

    /**
     * {@inheritdoc}
     * @param ServiceCallTask $task
     */
    public function execute(Task $task)
    {
        $task->execute();
    }

}
