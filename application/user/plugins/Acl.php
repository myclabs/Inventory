<?php
/**
 * @author     matthieu.napoli
 * @package    User
 * @subpackage Plugin
 */

/**
 * Plugin pour la vérification des ACL
 *
 * @package    User
 * @subpackage Plugin
 */
class User_Plugin_Acl extends User_Plugin_Abstract
{
    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function createUserRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return $this->aclService->isAllowed(
            $identity,
            User_Model_Action_Default::CREATE(),
            User_Model_Resource_Entity::loadByEntityName(User_Model_User::class)
        );
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function editUserRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        if ($request->getParam('id') !== null) {
            /** @var $user User_Model_User */
            $user = User_Model_User::load($request->getParam('id'));
        } else {
            $user = $identity;
        }
        return $this->aclService->isAllowed($identity, User_Model_Action_Default::EDIT(), $user);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function disableUserRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        $user = User_Model_User::load($request->getParam('id'));
        return $this->aclService->isAllowed($identity, User_Model_Action_Default::DELETE(), $user);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function enableUserRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        $user = User_Model_User::load($request->getParam('id'));
        return $this->aclService->isAllowed($identity, User_Model_Action_Default::UNDELETE(), $user);
    }

    /**
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function viewAllUsersRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        $resource = User_Model_Resource_Entity::loadByEntityName(User_Model_User::class);
        return $this->aclService->isAllowed($identity, User_Model_Action_Default::VIEW(), $resource);
    }

}
