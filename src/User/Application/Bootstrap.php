<?php

namespace User\Application;

use Core\TypeMapping\LocaleMapping;
use Core_Package_Bootstrap;
use DI\Container;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use User\Architecture\TypeMapping\ActionType;
use User\Application\Controller\Helper\AuthHelper;
use User\Domain\ACL\Authorization\RepositoryAuthorization;
use User\Domain\ACL\Authorization\UserAuthorization;
use User\Domain\ACL\Role;
use User\Domain\User;
use User\Domain\ACL\ACLService;
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

    protected function _initUserAuthorizationRepositories()
    {
        $this->container->set(
            ACLService::class,
            function (Container $c) {
                /** @var EntityManager $em */
                $em = $c->get(EntityManager::class);

                $aclService = new ACLService($em, $c->get(LoggerInterface::class));
                $aclService->setAuthorizationRepository(User::class, $em->getRepository(UserAuthorization::class));
                $aclService->setAuthorizationRepository(
                    'repository',
                    $em->getRepository(RepositoryAuthorization::class)
                );
                return $aclService;
            }
        );
    }
}
