<?php

return [

    Orga_Service_ETLStructure::class => DI\object()
            ->constructorParameter('defaultLocale', DI\link('translation.defaultLocale'))
            ->constructorParameter('locales', DI\link('translation.languages')),

];
