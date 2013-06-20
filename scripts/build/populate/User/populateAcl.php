<?php
/**
 * @package User
 */

/**
 * Insère les roles
 * @package User
 */
class User_PopulateAcl extends Core_Script_Action
{

    /**
     * {@inheritdoc}
     */
    public function runEnvironment($environment)
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        /** @var $entityManager \Doctrine\ORM\EntityManager */
        $entityManager = $entityManagers['default'];

        /** @var $aclService User_Service_ACL */
        $aclService = User_Service_ACL::getInstance();

        // ROLES

        // Création du role utilisateur.
        $roleUser = new User_Model_Role('user', 'Utilisateur');
        $roleUser->save();

        // Creation du role administrateur système.
        $roleAdmin = new User_Model_Role('sysadmin', 'Administrateur système');
        $roleAdmin->save();

        $entityManager->flush();


        // RESSOURCES

        // Application.
        $resourceReferential = new User_Model_Resource_Named('referential');
        $resourceReferential->save();

        // Tous les utilisateurs.
        $resourceAllUsers = new User_Model_Resource_Entity();
        $resourceAllUsers->setEntityName("User_Model_User");
        $resourceAllUsers->save();

        // Tous les utilisateurs normaux.
        $resourceAllNormalUsers = new User_Model_Resource_Entity();
        $resourceAllNormalUsers->setEntity($roleUser);
        $resourceAllNormalUsers->save();

        // Tous les admins.
        $resourceAllAdmins = new User_Model_Resource_Entity();
        $resourceAllAdmins->setEntity($roleAdmin);
        $resourceAllAdmins->save();

        // Toutes les organisations.
        $resourceAllOrganizations = new User_Model_Resource_Entity();
        $resourceAllOrganizations->setEntityName('Orga_Model_Organization');
        $resourceAllOrganizations->save();

        $entityManager->flush();


        // AUTORISATIONS

        // Utilisateurs peuvent consulter le référentiel.
        $aclService->allow($roleUser, User_Model_Action_Default::VIEW(), $resourceReferential);

        // Administrateurs peuvent consulter le référentiel.
        $aclService->allow($roleAdmin, User_Model_Action_Default::VIEW(), $resourceReferential);

        // Administrateurs peuvent administrer le référentiel.
        $aclService->allow($roleAdmin, User_Model_Action_Default::EDIT(), $resourceReferential);

        // Administrateur a accès à "consulter" tous les utilisateurs.
        $aclService->allow($roleAdmin, User_Model_Action_Default::VIEW(), $resourceAllUsers);
        // Administrateur a accès à "créer" utilisateur.
        $aclService->allow($roleAdmin, User_Model_Action_Default::CREATE(), $resourceAllUsers);
        // Administrateur a accès à "modifier" tous les utilisateurs normaux.
        $aclService->allow($roleAdmin, User_Model_Action_Default::EDIT(), $resourceAllNormalUsers);
        // Administrateur a accès à "supprimer" tous les utilisateurs.
        $aclService->allow($roleAdmin, User_Model_Action_Default::DELETE(), $resourceAllNormalUsers);
        // Administrateur a accès à "réactiver" tous les utilisateurs.
        $aclService->allow($roleAdmin, User_Model_Action_Default::UNDELETE(), $resourceAllNormalUsers);

        // Administrateurs peuvent voir, créer et modifier toutes les organisations.
        $aclService->allow($roleAdmin, User_Model_Action_Default::VIEW(), $resourceAllOrganizations);
        $aclService->allow($roleAdmin, User_Model_Action_Default::EDIT(), $resourceAllOrganizations);
        $aclService->allow($roleAdmin, User_Model_Action_Default::CREATE(), $resourceAllOrganizations);
        $aclService->allow($roleAdmin, User_Model_Action_Default::DELETE(), $resourceAllOrganizations);

        echo "\t\t\tACL created".PHP_EOL;
    }

}
