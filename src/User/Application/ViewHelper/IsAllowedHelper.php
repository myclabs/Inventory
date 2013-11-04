<?php

namespace User\Application\ViewHelper;

use User\Domain\ACL\Resource\Resource;
use Zend_Auth;
use Zend_View_Helper_Abstract;
use User\Domain\ACL\Action;
use Core_Model_Entity;
use User\Domain\ACL\ACLService;
use User\Domain\User;

/**
 * Helper pour tester les ACL
 * @author matthieu.napoli
 */
class IsAllowedHelper extends Zend_View_Helper_Abstract
{
    /**
     * @var \User\Domain\ACL\ACLService
     */
    private $aclService;

    public function __construct(ACLService $aclService)
    {
        $this->aclService = $aclService;
    }

    /**
     * Vérifie une autorisation d'accès à une ressource pour l'utilisateur connecté
     *
     * @param \User\Domain\ACL\Action                     $action Action demandée
     * @param \User\Domain\ACL\Resource\Resource|Core_Model_Entity $target Ressource ou entité
     *
     * @return boolean
     */
    public function isAllowed(Action $action, $target)
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            return false;
        }

        /** @var User $user */
        $user = User::load($auth->getIdentity());

        return $this->aclService->isAllowed($user, $action, $target);
    }

}