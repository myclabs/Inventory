<?php

namespace Core\Work;

use Core\Work\Task;

/**
 * @author matthieu.napoli
 */
abstract class Worker
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
     * @param Task $task
     * @return mixed Résultat
     */
    public abstract function execute(Task $task);
}
