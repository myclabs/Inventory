<?php
/**
 * @author  matthieu.napoli
 * @package Core
 */

/**
 * Service gérant l'exécution de tâches
 *
 * @package Core
 */
interface Core_Work_Dispatcher
{

    /**
     * Lance l'exxécution d'une tâche de manière synchrone (i.e. attends la fin de son exécution)
     *
     * @param Core_Work_Task $task
     * @return mixed Résultat de la tache
     */
    public function run(Core_Work_Task $task);

    /**
     * Lance l'exxécution d'une tâche de manière asynchrone (i.e. n'attends pas la fin de son exécution)
     *
     * @param Core_Work_Task $task
     * @return boolean True: tache exécutée, False: tache en cours d'exécution
     */
    public function runBackground(Core_Work_Task $task);

    /**
     * Enregistre un worker
     *
     * @param Core_Work_Worker $worker
     */
    public function registerWorker(Core_Work_Worker $worker);

    /**
     * Fait travailler les workers pour exécuter les tâches
     */
    public function work();

}
