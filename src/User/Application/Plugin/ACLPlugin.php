<?php

namespace User\Application\Plugin;

use User\Domain\ACL\Action;
use User\Domain\ACL\Resource\EntityResource;
use User\Domain\ACL\SecurityIdentity;
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
     * @param SecurityIdentity                 $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function createUserRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclService->isAllowed(
            $identity,
            Action::CREATE(),
            EntityResource::loadByEntityName(User::class)
        );
    }

    /**
     * @param SecurityIdentity                 $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function editUserRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
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
     * @param SecurityIdentity                 $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function disableUserRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        $user = User::load($request->getParam('id'));
        return $this->aclService->isAllowed($identity, Action::DELETE(), $user);
    }

    /**
     * @param SecurityIdentity                 $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function enableUserRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        $user = User::load($request->getParam('id'));
        return $this->aclService->isAllowed($identity, Action::UNDELETE(), $user);
    }

    /**
     * @param SecurityIdentity                 $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function viewAllUsersRule(SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        $resource = EntityResource::loadByEntityName(User::class);
        return $this->aclService->isAllowed($identity, Action::VIEW(), $resource);
    }
}
