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
        $container = \Core\ContainerSingleton::getContainer();

        $commandBase = $container->get('mysqlBin.path')
            . ' -h' . $container->get('db.host')
            . ' -u' . $container->get('db.user');
        if (!empty($container->get('db.password'))) {
            $commandBase .= ' -p' . $container->get('db.password');
        }
        if (!empty($container->get('db.port'))) {
            $commandBase .= ' --port=' . $container->get('db.port');
        }

        $filePath = PACKAGE_PATH . '/behat/fixtures/' . $fileName;

        $command = $commandBase . ' ' . $container->get('db.name') . ' < "' . $filePath . '"';

        shell_exec($command);
    }
}
