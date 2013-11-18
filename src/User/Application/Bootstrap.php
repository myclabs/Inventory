<?php

namespace User\Application;

use Core\TypeMapping\LocaleMapping;
use Core_Package_Bootstrap;
use Doctrine\DBAL\Types\Type;
use User\Architecture\TypeMapping\ActionType;
use User\Application\Controller\Helper\AuthHelper;
use User\Domain\ACL\Role\Role;
use Zend_Controller_Action_HelperBroker;

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
    }
}
