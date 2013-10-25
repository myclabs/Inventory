<?php
use User\Domain\ACL\Role;
use User\Domain\UserService;

/**
 * @package User
 */


/**
 * Remplissage de la base de données avec des données de test
 * @package User
 */
class User_PopulateUser extends Core_Script_Action
{

    /**
     * {@inheritdoc}
     */
    public function runEnvironment($environment)
    {
        /** @var DI\Container $container */
        $container = Zend_Registry::get('container');

        $entityManagers = Zend_Registry::get('EntityManagers');
        /** @var $entityManager \Doctrine\ORM\EntityManager */
        $entityManager = $entityManagers['default'];

        /** @var $userService UserService */
        $userService = $container->get(UserService::class);

        // Charge les roles
        $roleAdmin = Role::loadByRef('sysadmin');

        // Crée un admin
        $admin = $userService->createUser('admin@myc-sense.com', 'myc-53n53');
        $admin->setLastName('Système');
        $admin->setFirstName('Administrateur');
        $admin->addRole($roleAdmin);
        $admin->save();

        $entityManager->flush();

        echo "\t\t\t'admin' created".PHP_EOL;
    }

}
