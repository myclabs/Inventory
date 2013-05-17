<?php
/**
 * @author  valentin.claras
 * @author  matthieu.napoli
 * @package Core
 */

/**
 * Exécute l'appel d'une méthode d'une entité.
 *
 * @package Core
 */
class Orga_Work_WorkerGranularity extends Core_Work_Worker
{

    /**
     * {@inheritdoc}
     */
    public function getTaskType()
    {
        return 'Orga_Work_Task_AddGranularity';
    }

    /**
     * {@inheritdoc}
     * @param Core_Work_ServiceCall_Task $task
     */
    public function execute(Core_Work_Task $task)
    {
        $task->execute();
    }

}
