<?php

use Core\TypeMapping\LocaleMapping;
use Doctrine\DBAL\Types\Type;
use User\ACL\EntityManagerListener;
use User\ACL\TypeMapping\ActionType;

/**
 * Bootstrap
 * @author matthieu.napoli
 * @author valentin.claras
 */
class User_Bootstrap extends Core_Package_Bootstrap
{

    /**
     * Enregistrement des Action Helpers
     */
    protected function _initUserActionHelpers()
    {
        Zend_Controller_Action_HelperBroker::addHelper(new User_Controller_Helper_Auth());
    }

    /**
     * Initialise le mapping des types en BDD
     */
    protected function _initUserTypeMapping()
    {
        Type::addType(LocaleMapping::TYPE_NAME, LocaleMapping::class);
        Type::addType(ActionType::TYPE_NAME, ActionType::class);
    }

    /**
     * Configuration pour les ressources "Utilisateur" des ACL
     */
    protected function _initACLUserResourceTreeTraverser()
    {
        /** @var $usersResourceTreeTraverser User_Service_ACL_UsersResourceTreeTraverser */
        $usersResourceTreeTraverser = $this->container->get(User_Service_ACL_UsersResourceTreeTraverser::class);

        /** @var $aclService User_Service_ACL */
        $aclService = $this->container->get(User_Service_ACL::class);
        $aclService->setResourceTreeTraverser(User_Model_User::class, $usersResourceTreeTraverser);
        $aclService->setResourceTreeTraverser(User_Model_Role::class, $usersResourceTreeTraverser);
    }

    /**
     * Listener de l'entity manager pour le filtre des ACL
     */
    protected function _initACLEntityManagerListener()
    {
        if (! Zend_Registry::isRegistered('EntityManagers')) {
            return;
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        /** @var $entityManager Doctrine\ORM\EntityManager */
        $entityManager = $entityManagers['default'];
        $events = [
            Doctrine\ORM\Events::onFlush,
            Doctrine\ORM\Events::postFlush,
        ];

        /** @var EntityManagerListener $aclEntityManagerListener */
        $aclEntityManagerListener = $this->container->get(EntityManagerListener::class);

        $entityManager->getEventManager()->addEventListener($events, $aclEntityManagerListener);
    }

}
