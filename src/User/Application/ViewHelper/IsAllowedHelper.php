<?php

namespace User\Application\ViewHelper;

use MyCLabs\ACL\ACL;
use MyCLabs\ACL\Model\ResourceInterface;
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
     * @var ACL
     */
    private $acl;

    public function __construct(ACL $acl)
    {
        $this->acl = $acl;
    }

    /**
     * Vérifie une autorisation d'accès à une ressource pour l'utilisateur connecté
     *
     * @param string            $action Action demandée
     * @param ResourceInterface $target Ressource
     *
     * @return boolean
     */
    public function isAllowed($action, ResourceInterface $target)
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            return false;
        }

        /** @var User $user */
        $user = User::load($auth->getIdentity());

        return $this->acl->isAllowed($user, $action, $target);
    }
}
