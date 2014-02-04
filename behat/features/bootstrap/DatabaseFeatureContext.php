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
     * @BeforeScenario @dbForTestDWUpToDate
     */
    public function loadForTestDWUpToDateDatabase()
    {
        self::loadFileToDatabase('forTestDWUpToDate.sql');
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
