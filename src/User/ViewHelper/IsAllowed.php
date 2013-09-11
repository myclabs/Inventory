<?php

/**
 * Helper pour tester les ACL
 * @author matthieu.napoli
 */
class User_ViewHelper_IsAllowed extends Zend_View_Helper_Abstract
{
    /**
     * @var User_Service_ACL
     */
    private $aclService;

    public function __construct(User_Service_ACL $aclService)
    {
        $this->aclService = $aclService;
    }

    /**
     * Vérifie une autorisation d'accès à une ressource pour l'utilisateur connecté
     *
     * @param User_Model_Action                     $action   Action demandée
     * @param User_Model_Resource|Core_Model_Entity $target   Ressource ou entité
     *
     * @return boolean
     */
    public function isAllowed(User_Model_Action $action, $target)
    {
        $auth = Zend_Auth::getInstance();
        if (! $auth->hasIdentity()) {
            return false;
        }

        /** @var User_Model_User $user */
        $user = User_Model_User::load($auth->getIdentity());

        return $this->aclService->isAllowed($user, $action, $target);
    }

}