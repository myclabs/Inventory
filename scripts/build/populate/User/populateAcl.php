<?php
use User\Domain\ACL\Action\DefaultAction;
use User\Domain\ACL\Resource\EntityResource;
use User\Domain\ACL\Resource\NamedResource;
use User\Domain\ACL\Role;
use User\Domain\ACL\ACLService;
use User\Domain\User;

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
        /** @var DI\Container $container */
        $container = Zend_Registry::get('container');

        $entityManagers = Zend_Registry::get('EntityManagers');
        /** @var $entityManager \Doctrine\ORM\EntityManager */
        $entityManager = $entityManagers['default'];

        /** @var $aclService ACLService */
        $aclService = $container->get(ACLService::class);

        // ROLES

        // Création du role utilisateur.
        $roleUser = new Role('user', 'Utilisateur');
        $roleUser->save();

        // Creation du role administrateur système.
        $roleAdmin = new Role('sysadmin', 'Administrateur système');
        $roleAdmin->save();

        $entityManager->flush();


        // RESSOURCES

        // Application.
        $resourceReferential = new NamedResource('referential');
        $resourceReferential->save();

        // Tous les utilisateurs.
        $resourceAllUsers = new EntityResource();
        $resourceAllUsers->setEntityName(User::class);
        $resourceAllUsers->save();

        // Tous les utilisateurs normaux.
        $resourceAllNormalUsers = new EntityResource();
        $resourceAllNormalUsers->setEntity($roleUser);
        $resourceAllNormalUsers->save();

        // Tous les admins.
        $resourceAllAdmins = new EntityResource();
        $resourceAllAdmins->setEntity($roleAdmin);
        $resourceAllAdmins->save();

        // Toutes les organisations.
        $resourceAllOrganizations = new EntityResource();
        $resourceAllOrganizations->setEntityName(Orga_Model_Organization::class);
        $resourceAllOrganizations->save();

        $entityManager->flush();


        // AUTORISATIONS

        // Utilisateurs peuvent consulter le référentiel.
        $aclService->allow($roleUser, DefaultAction::VIEW(), $resourceReferential);

        // Administrateurs peuvent consulter le référentiel.
        $aclService->allow($roleAdmin, DefaultAction::VIEW(), $resourceReferential);

        // Administrateurs peuvent administrer le référentiel.
        $aclService->allow($roleAdmin, DefaultAction::EDIT(), $resourceReferential);

        // Administrateur a accès à "consulter" tous les utilisateurs.
        $aclService->allow($roleAdmin, DefaultAction::VIEW(), $resourceAllUsers);
        // Administrateur a accès à "créer" utilisateur.
        $aclService->allow($roleAdmin, DefaultAction::CREATE(), $resourceAllUsers);
        // Administrateur a accès à "modifier" tous les utilisateurs normaux.
        $aclService->allow($roleAdmin, DefaultAction::EDIT(), $resourceAllNormalUsers);
        // Administrateur a accès à "supprimer" tous les utilisateurs.
        $aclService->allow($roleAdmin, DefaultAction::DELETE(), $resourceAllNormalUsers);
        // Administrateur a accès à "réactiver" tous les utilisateurs.
        $aclService->allow($roleAdmin, DefaultAction::UNDELETE(), $resourceAllNormalUsers);

        // Administrateurs peuvent voir, créer et modifier toutes les organisations.
        $aclService->allow($roleAdmin, DefaultAction::VIEW(), $resourceAllOrganizations);
        $aclService->allow($roleAdmin, DefaultAction::EDIT(), $resourceAllOrganizations);
        $aclService->allow($roleAdmin, DefaultAction::CREATE(), $resourceAllOrganizations);
        $aclService->allow($roleAdmin, DefaultAction::DELETE(), $resourceAllOrganizations);

        echo "\t\t\tACL created".PHP_EOL;
    }

}
