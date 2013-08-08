<?php
/**
 * @package    User
 * @subpackage Controller
 */

/**
 * Contrôleur par défaut des utilisateurs.
 * @package    User
 * @subpackage Controller
 */
class User_IndexController extends Core_Controller
{

    /**
     * Par défaut : redirige vers l'action de list des utilisateurs.
     */
    public function indexAction()
    {
        $this->redirect('/user/profile/list');
    }

}
