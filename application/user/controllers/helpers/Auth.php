<?php
/**
 * @package    User
 * @subpackage Controller
 */

/**
 * Classe permettant de créer les Acl pour le module User
 *
 * Classe Helper d'action (Action Helper)
 *
 * @package    User
 * @subpackage Controller
 */
class User_Controller_Helper_Auth extends Zend_Controller_Action_Helper_Abstract
{

    /**
     * Retourne l'utilisateur connecté
     *
     * @return User_Model_User|null Utilisateur connecté
     */
    public function direct()
    {
        return $this->getLoggedInUser();
    }

    /**
     * Retourne l'utilisateur connecté
     *
     * @return User_Model_User|null Utilisateur connecté
     */
    public function getLoggedInUser()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            return User_Model_User::load($auth->getIdentity());
        }
        return null;
    }

}
