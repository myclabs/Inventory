<?php

/**
 * Crée la BDD
 *
 * @author valentin.claras
 * @author matthieu.napoli
 */
class Inventory_Create extends Core_Script_Action
{
    protected function runEnvironment($environment)
    {
        $container = \Core\ContainerSingleton::getContainer();

        // Suppression de l'ancienne base, puis création de la nouvelle.
        //  Permet de s'assurer que les anciennes tables ont bien été supprimé.
        // CLI call
        //@todo Création de la base : trouver une façon de faire sans le mysqlBinPath.
        $commandBase = $container->get('mysqlBin.path')
            . ' -h' . $container->get('db.host')
            . ' -u' . $container->get('db.user');
        if (! empty($container->get('db.password'))) {
            $commandBase .= ' -p' . $container->get('db.password');
        }
        if (! empty($container->get('db.port'))) {
            $commandBase .= ' --port=' . $container->get('db.port');
        }

        $commandDrop = $commandBase . ' -e "DROP DATABASE IF EXISTS ' . $container->get('db.name') . '"';
        shell_exec($commandDrop);
        $commandCreate = $commandBase . ' -e "CREATE DATABASE ' . $container->get('db.name') . '"';
        shell_exec($commandCreate);

        echo "\t\tBase " . $container->get('db.name') . " created.".PHP_EOL;
    }
}
