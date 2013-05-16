<?php
/**
 * @author  matthieu.napoli
 * @package Core
 */

/**
 * @package Core
 */
abstract class Core_Work_Worker
{

    /**
     * Retourne le type des tâches que le worker exécute
     *
     * @return string
     */
    public abstract function getTaskType();

    /**
     * Exécute une tâche
     *
     * @param Core_Work_Task $task
     * @return mixed Résultat
     */
    public abstract function execute(Core_Work_Task $task);

}
