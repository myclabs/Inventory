<?php

namespace User\Application\Plugin;

use User\Domain\ACL\Actions;
use MyCLabs\ACL\Model\ClassResource;
use User\Domain\User;
use Zend_Controller_Request_Abstract;

/**
 * Plugin pour la vérification des ACL
 *
 * @author matthieu.napoli
 */
class ACLPlugin extends AbstractACLPlugin
{
    /**
     * @param User                 $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function createUserRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclManager->isAllowed(
            $identity,
            Actions::CREATE,
            new ClassResource(User::class)
        );
    }

    /**
     * @param User                 $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function editUserRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        if ($request->getParam('id') !== null) {
            /** @var $user User */
            $user = User::load($request->getParam('id'));
        } else {
            $user = $identity;
        }
        return $this->aclManager->isAllowed($identity, Actions::EDIT, $user);
    }

    /**
     * @param User                 $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function disableUserRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $user = User::load($request->getParam('id'));
        return $this->aclManager->isAllowed($identity, Actions::DELETE, $user);
    }

    /**
     * @param User                 $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function enableUserRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $user = User::load($request->getParam('id'));
        return $this->aclManager->isAllowed($identity, Actions::UNDELETE, $user);
    }

    /**
     * @param User                 $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function viewAllUsersRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclManager->isAllowed($identity, Actions::VIEW, new ClassResource(User::class));
    }
}
