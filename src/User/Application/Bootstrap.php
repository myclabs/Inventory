<?php

namespace User\Application;

use Core\TypeMapping\LocaleMapping;
use Core_Package_Bootstrap;
use Doctrine\DBAL\Types\Type;
use User\Domain\ACL\EntityManagerListener;
use User\Architecture\TypeMapping\ActionType;
use User\Application\Controller\Helper\AuthHelper;
use User\Domain\ACL\Role;
use User\Domain\User;
use User\Domain\ACL\ACLService;
use User\Domain\ACL\UsersResourceTreeTraverser;
use Zend_Controller_Action_HelperBroker;
use Zend_Registry;

/**
 * @author matthieu.napoli
 * @author valentin.claras
 */
class Bootstrap extends Core_Package_Bootstrap
{
    /**
     * Enregistrement des Action Helpers
     */
    protected function _initUserActionHelpers()
    {
        Zend_Controller_Action_HelperBroker::addHelper($this->container->get(AuthHelper::class));
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
        /** @var $usersResourceTreeTraverser UsersResourceTreeTraverser */
        $usersResourceTreeTraverser = $this->container->get(UsersResourceTreeTraverser::class);

        /** @var $aclService ACLService */
        $aclService = $this->container->get(ACLService::class);
        $aclService->setResourceTreeTraverser(User::class, $usersResourceTreeTraverser);
        $aclService->setResourceTreeTraverser(Role::class, $usersResourceTreeTraverser);
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
        /** @var $entityManager \Doctrine\ORM\EntityManager */
        $entityManager = $entityManagers['default'];
        $events = [
            \Doctrine\ORM\Events::onFlush,
            \Doctrine\ORM\Events::postFlush,
        ];

        /** @var \User\Domain\ACL\EntityManagerListener $aclEntityManagerListener */
        $aclEntityManagerListener = $this->container->get(EntityManagerListener::class);

        $entityManager->getEventManager()->addEventListener($events, $aclEntityManagerListener);
    }
}
