<?php
/**
 * @author     matthieu.napoli
 * @package    Inventory
 * @subpackage Plugin
 */

use DI\Container;

/**
 * Configure l'utilisateur connecté
 *
 * @package    Inventory
 * @subpackage Plugin
 */
class Inventory_Plugin_LoggedInUserConfigurator extends Zend_Controller_Plugin_Abstract
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Méthode appelée avant tout routage
     *
     * @param Zend_Controller_Request_Abstract $request Requête HTTP
     */
    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
        $auth = Zend_Auth::getInstance();

        if ($auth->hasIdentity()) {
            $user = User_Model_User::load($auth->getIdentity());
            $this->container->set('loggedInUser', $user);
        }
    }
}
