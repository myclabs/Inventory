<?php

use User\Application\ViewHelper\IsAllowedHelper;
use User\Domain\UserService;

return [
    UserService::class     => DI\object()
            ->methodParameter('__construct', 'contactEmail', DI\link('emails.contact.adress'))
            ->methodParameter('__construct', 'noReplyEmail', DI\link('emails.noreply.adress'))
            ->methodParameter('__construct', 'noReplyName', DI\link('emails.noreply.name'))
            ->methodParameter('__construct', 'applicationUrl', DI\link('application.url')),
    IsAllowedHelper::class => DI\object()
            ->lazy(),
];
