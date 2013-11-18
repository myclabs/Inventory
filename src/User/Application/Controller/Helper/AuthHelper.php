<?php

namespace User\Application\Controller\Helper;

use Zend_Auth;
use Zend_Controller_Action_Helper_Abstract;
use User\Domain\User;

/**
 * Classe permettant de créer les Acl pour le module User
 *
 * Classe Helper d'action (Action Helper)
 *
 * @author matthieu.napoli
 */
class AuthHelper extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Retourne l'utilisateur connecté
     *
     * @return \User\Domain\User|null Utilisateur connecté
     */
    public function direct()
    {
        return $this->getLoggedInUser();
    }

    /**
     * Retourne l'utilisateur connecté
     *
     * @return \User\Domain\User|null Utilisateur connecté
     */
    public function getLoggedInUser()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            return User::load($auth->getIdentity());
        }
        return null;
    }

    public function getName()
    {
        return 'Auth';
    }
}
