<?php
/**
 * @author matthieu.napoli
 */

trait DatabaseFeatureContext
{
    /**
     * Sauvegarde la BDD actuellement chargée.
     * @var string
     */
    private static $currentDb;

    /**
     * Est-ce qu'il faut importer la BDD.
     * @var bool
     */
    private static $needDbImport = true;

    /**
     * @BeforeScenario @dbEmpty
     */
    public function loadEmptyDatabase()
    {
        if (self::$currentDb == 'dbEmpty' && self::$needDbImport == false) {
            return;
        }
        self::loadFileToDatabase('emptyOneUser.sql');
        self::$currentDb = 'dbEmpty';
    }
    /**
     * @BeforeScenario @dbFull
     */
    public function loadFilledDatabase()
    {
        if (self::$currentDb == 'dbFull' && self::$needDbImport == false) {
            return;
        }
        self::loadFileToDatabase('full.sql');
        self::$currentDb = 'dbFull';
    }
    /**
     * @BeforeScenario @dbForTestDWUpToDate
     */
    public function loadForTestDWUpToDateDatabase()
    {
        if (self::$currentDb == 'dbForTestDWUpToDate' && self::$needDbImport == false) {
            return;
        }
        self::loadFileToDatabase('forTestDWUpToDate.sql');
        self::$currentDb = 'dbForTestDWUpToDate';
    }

    /**
     * @BeforeScenario
     */
    public function scenarioUpdatesDb()
    {
        self::$needDbImport = true;
    }

    /**
     * Les scénarios marqués comme readOnly n'entrainent pas un rechargement de la BDD.
     * @AfterScenario @readOnly
     */
    public function scenarioWasReadOnly()
    {
        self::$needDbImport = false;
    }


    private static function loadFileToDatabase($fileName)
    {
        $environment = 'developpement';

        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', $environment);
        $configShared = new Zend_Config_Ini(APPLICATION_PATH . '/configs/shared.ini', $environment, true);
        $configShared->merge($config);
        $config = $configShared;

        $connectionSettings = $config->doctrine->default->connection;

        $commandBase = $config->mysqlBin->path
            . ' -h' . $connectionSettings->host
            . ' -u' . $connectionSettings->user;
        if (!empty($connectionSettings->password)) {
            $commandBase .= ' -p' . $connectionSettings->password;
        }
        if (!empty($connectionSettings->port)) {
            $commandBase .= ' --port=' . $connectionSettings->port;
        }

        $filePath = PACKAGE_PATH . '/behat/fixtures/' . $fileName;

        $command = $commandBase . ' ' . $connectionSettings->dbname . ' < "' . $filePath . '"';

        shell_exec($command);
    }
}
