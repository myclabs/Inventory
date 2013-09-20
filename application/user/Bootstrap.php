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
        Type::addType(LocaleMapping::TYPE_NAME, 'Core\TypeMapping\LocaleMapping');
        Type::addType(ActionType::TYPE_NAME, 'User\ACL\TypeMapping\ActionType');
    }

    /**
     * Configuration pour les ressources "Utilisateur" des ACL
     */
    protected function _initACLUserResourceTreeTraverser()
    {
        /** @var $usersResourceTreeTraverser User_Service_ACL_UsersResourceTreeTraverser */
        $usersResourceTreeTraverser = $this->container->get('User_Service_ACL_UsersResourceTreeTraverser');

        /** @var $aclService User_Service_ACL */
        $aclService = $this->container->get('User_Service_ACL');
        $aclService->setResourceTreeTraverser("User_Model_User", $usersResourceTreeTraverser);
        $aclService->setResourceTreeTraverser("User_Model_Role", $usersResourceTreeTraverser);
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
        $aclEntityManagerListener = $this->container->get('User\ACL\EntityManagerListener');

        $entityManager->getEventManager()->addEventListener($events, $aclEntityManagerListener);
    }

}
