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
class Inventory_Work_Worker extends Core_Work_Worker
{

    /**
     * {@inheritdoc}
     */
    public function getTaskType()
    {
        return 'Inventory_Work_Task_SetGranularityDataProviderCellsGenerateDWCubes';
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
