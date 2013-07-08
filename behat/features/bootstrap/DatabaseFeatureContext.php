<?php
/**
 * @author matthieu.napoli
 */

trait DatabaseFeatureContext
{
    /**
     * @BeforeScenario @dbEmpty
     */
    public function loadEmptyDatabase()
    {
        self::loadFileToDatabase('emptyOneUser.sql');
    }
    /**
     * @BeforeScenario @dbFull
     */
    public function loadFilledDatabase()
    {
        self::loadFileToDatabase('full.sql');
    }
    /**
     * @BeforeScenario @dbOneOrganization
     */
    public function loadOneOrganizationDatabase()
    {
        self::loadFileToDatabase('oneOrganization.sql');
    }
    /**
     * @BeforeScenario @dbOneOrganizationWithAxes
     */
    public function loadOneOrganizationWithAxesDatabase()
    {
        self::loadFileToDatabase('oneOrganizationWithAxes.sql');
    }
    /**
     * @BeforeScenario @dbWithClassifAxesIndicatorsContexts
     */
    public function loadWithClassifAxesIndicatorsContextsDatabase()
    {
        self::loadFileToDatabase('withClassifAxesIndicatorsContexts.sql');
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
