<?php

/**
 * Crée la BDD
 *
 * @author     valentin.claras
 * @author     matthieu.napoli
 */
class Inventory_Create extends Core_Script_Action
{
    /**
     * Run the script for a specific environment.
     *
     * @param string $environment
     */
    protected function runEnvironment($environment)
    {
        // Récupération de la configuration de la connexion dans l'application.ini.
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', $environment);
        if (file_exists(APPLICATION_PATH . '/configs/shared.ini')) {
            $configShared = new Zend_Config_Ini(APPLICATION_PATH . '/configs/shared.ini', $environment, true);
            $configShared->merge($config);
            $config = $configShared;
        }

        $connectionSettings = $config->doctrine->default->connection;

        // Suppression de l'ancienne base, puis création de la nouvelle.
        //  Permet de s'assurer que les anciennes tables ont bien été supprimé.
        // CLI call
        //@todo Création de la base : trouver une façon de faire sans le mysqlBinPath.
        $commandBase = $config->mysqlBin->path.' -h'.$connectionSettings->host.' -u'.$connectionSettings->user;
        if (!empty($connectionSettings->password)) {
            $commandBase .= ' -p'.$connectionSettings->password;
        }
        if (!empty($connectionSettings->port)) {
            $commandBase .= ' --port='.$connectionSettings->port;
        }

        $commandDrop = $commandBase.' -e "DROP DATABASE IF EXISTS '.$connectionSettings->dbname.'"';
        shell_exec($commandDrop);
        $commandCreate = $commandBase.' -e "CREATE DATABASE '.$connectionSettings->dbname.'"';
        shell_exec($commandCreate);

        echo "\t\tBase {$connectionSettings->dbname} created.".PHP_EOL;
    }
}
