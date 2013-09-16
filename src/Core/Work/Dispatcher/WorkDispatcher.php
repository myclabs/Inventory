<?php

namespace Core\Work\Dispatcher;

use Core\Work\Task;
use Core\Work\Worker;

/**
 * Service gérant l'exécution de tâches
 *
 * @author matthieu.napoli
 */
interface WorkDispatcher
{
    /**
     * Lance l'exxécution d'une tâche de manière synchrone (i.e. attends la fin de son exécution)
     *
     * @param Task $task
     * @return mixed Résultat de la tache
     */
    public function run(Task $task);

    /**
     * Lance l'exxécution d'une tâche de manière asynchrone (i.e. n'attends pas la fin de son exécution)
     *
     * @param Task $task
     * @return void Pas de résultat retourné
     */
    public function runBackground(Task $task);

    /**
     * Enregistre un worker
     *
     * @param Worker $worker
     */
    public function registerWorker(Worker $worker);

    /**
     * Fait travailler les workers pour exécuter les tâches
     */
    public function work();
}
