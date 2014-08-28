<?php

use Orga\Application\Service\Workspace\FreeApplicationRegisteringService;
use Orga\Domain\Service\ETL\ETLDataInterface;
use Orga\Domain\Service\ETL\ETLDataService;
use Orga\Domain\Service\ETL\ETLStructureInterface;
use Orga\Domain\Service\ETL\ETLStructureService;
use Orga\Domain\Service\Cell\Input\CellInputUpdaterInterface;
use Orga\Domain\Service\Cell\Input\CellInputService;

return [

    ETLStructureInterface::class => DI\object(ETLStructureService::class)
            ->constructorParameter('defaultLocale', DI\link('translation.defaultLocale'))
            ->constructorParameter('locales', DI\link('translation.languages')),

    ETLDataInterface::class => DI\object(ETLDataService::class),

    CellInputUpdaterInterface::class => DI\object(CellInputService::class),

    FreeApplicationRegisteringService::class => DI\object(FreeApplicationRegisteringService::class)
            ->constructorParameter('applicationUrl', DI\link('application.url')),

];
