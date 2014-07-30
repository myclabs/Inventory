<?php

namespace Orga\Domain\Service;

use Core\ContainerSingleton;
use Orga\Domain\Service\Cell\Input\CellInputUpdaterInterface;
use Orga\Domain\Service\ETL\ETLDataInterface;
use Orga\Domain\Service\ETL\ETLStructureInterface;
use Orga\Domain\Service\ETL\OrgaReportFactory;

class OrgaDomainHelper extends ContainerSingleton
{
    /**
     * @return ETLStructureInterface
     */
    public static function getETLStructureService()
    {
        return self::getContainer()->get(ETLStructureInterface::class);
    }

    /**
     * @return ETLDataInterface
     */
    public static function getETLData()
    {
        return self::getContainer()->get(ETLDataInterface::class);
    }

    /**
     * @return CellInputUpdaterInterface
     */
    public static function getCellInputUpdater()
    {
        return self::getContainer()->get(CellInputUpdaterInterface::class);
    }

    /**
     * @return OrgaReportFactory
     */
    public static function getOrgaReportFactory()
    {
        return self::getContainer()->get(OrgaReportFactory::class);
    }

    /**
     * @return OrgaACLManager
     */
    public static function getOrgaACLManager()
    {
        return self::getContainer()->get(OrgaACLManager::class);
    }
}
