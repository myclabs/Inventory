<?php
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
        /** @var $userService User_Service_User */
        $userService = User_Service_User::getInstance();
        $entityManagers = Zend_Registry::get('EntityManagers');
        /** @var $entityManager \Doctrine\ORM\EntityManager */
        $entityManager = $entityManagers['default'];

        // Charge les roles
        $roleAdmin = User_Model_Role::loadByRef('sysadmin');

        // Crée un admin
        $admin = $userService->createUser('admin', 'myc-53n53');
        $admin->setLastName('Administrateur');
        $admin->addRole($roleAdmin);
        $admin->save();

        $entityManager->flush();

        echo "\t\t\t'admin' created".PHP_EOL;
    }

}
