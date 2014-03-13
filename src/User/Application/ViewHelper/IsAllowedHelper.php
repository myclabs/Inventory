<?php

namespace User\Application\ViewHelper;

use MyCLabs\ACL\ACLManager;
use MyCLabs\ACL\Model\EntityResourceInterface;
use Zend_Auth;
use Zend_View_Helper_Abstract;
use User\Domain\User;

/**
 * Helper pour tester les ACL
 * @author matthieu.napoli
 */
class IsAllowedHelper extends Zend_View_Helper_Abstract
{
    /**
     * @var ACLManager
     */
    private $aclManager;

    public function __construct(ACLManager $aclManager)
    {
        $this->aclManager = $aclManager;
    }

    /**
     * Vérifie une autorisation d'accès à une ressource pour l'utilisateur connecté
     *
     * @param string                                              $action Action demandée
     * @param \MyCLabs\ACL\Model\Resource|EntityResourceInterface $target Ressource ou entité
     *
     * @return boolean
     */
    public function isAllowed($action, $target)
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            return false;
        }

        /** @var User $user */
        $user = User::load($auth->getIdentity());

        return $this->aclManager->isAllowed($user, $action, $target);
    }
}
