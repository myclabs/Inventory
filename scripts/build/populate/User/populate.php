<?php

use Doctrine\ORM\EntityManager;
use User\Domain\ACL\Resource\NamedResource;
use User\Domain\ACL\Role\AdminRole;
use User\Domain\User;
use User\Domain\UserService;

class User_Populate extends Core_Script_Populate
{
    public function populateEnvironment($environment)
    {
        /** @var DI\Container $container */
        $container = Zend_Registry::get('container');

        /** @var EntityManager $entityManager */
        $entityManager = $container->get(EntityManager::class);

        /** @var $userService UserService */
        $userService = $container->get(UserService::class);

        // Ressource "Repository"
        $repository = new NamedResource('repository');
        $repository->save();
        // Ressource abstraite "tous les utilisateurs"
        $allUsers = new NamedResource(User::class);
        $allUsers->save();
        // Ressource abstraite "toutes les organisations"
        $allOrganizations = new NamedResource(Orga_Model_Organization::class);
        $allOrganizations->save();

        $entityManager->flush();

        // Crée un admin
        $admin = $userService->createUser('admin@myc-sense.com', 'myc-53n53');
        $admin->setLastName('Système');
        $admin->setFirstName('Administrateur');
        $admin->addRole(new AdminRole($admin));
        $admin->save();

        $entityManager->flush();

        echo "\t\tUsers ($environment) : OK".PHP_EOL;
    }
}
