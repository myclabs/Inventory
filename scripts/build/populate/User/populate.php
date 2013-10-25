<?php

use User\Domain\ACL\Role\AdminRole;
use User\Domain\UserService;

class User_Populate extends Core_Script_Populate
{
    public function populateEnvironment($environment)
    {
        /** @var DI\Container $container */
        $container = Zend_Registry::get('container');

        /** @var $userService UserService */
        $userService = $container->get(UserService::class);

        // Crée un admin
        $admin = $userService->createUser('admin@myc-sense.com', 'myc-53n53');
        $admin->setLastName('Système');
        $admin->setFirstName('Administrateur');
        $admin->addRole(new AdminRole($admin));
        $admin->save();

        echo "\t\tUsers ($environment) : OK".PHP_EOL;
    }
}
