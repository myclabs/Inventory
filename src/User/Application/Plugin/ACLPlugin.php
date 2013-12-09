<?php

namespace User\Application\Plugin;

use User\Domain\ACL\Action;
use User\Domain\ACL\Resource\NamedResource;
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
        return $this->aclService->isAllowed(
            $identity,
            Action::CREATE(),
            NamedResource::loadByName(User::class)
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
        return $this->aclService->isAllowed($identity, Action::EDIT(), $user);
    }

    /**
     * @param User                 $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function disableUserRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $user = User::load($request->getParam('id'));
        return $this->aclService->isAllowed($identity, Action::DELETE(), $user);
    }

    /**
     * @param User                 $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function enableUserRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $user = User::load($request->getParam('id'));
        return $this->aclService->isAllowed($identity, Action::UNDELETE(), $user);
    }

    /**
     * @param User                 $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function viewAllUsersRule(User $identity, Zend_Controller_Request_Abstract $request)
    {
        $resource = NamedResource::loadByName(User::class);
        return $this->aclService->isAllowed($identity, Action::VIEW(), $resource);
    }
}
