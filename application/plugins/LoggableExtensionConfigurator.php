<?php
/**
 * @author     matthieu.napoli
 * @package    Inventory
 * @subpackage Plugin
 */

use Gedmo\Loggable\LoggableListener;

/**
 * Configure l'extension doctrine Loggable
 *
 * @package    Inventory
 * @subpackage Plugin
 */
class Inventory_Plugin_LoggableExtensionConfigurator extends Zend_Controller_Plugin_Abstract
{

    /**
     * @var LoggableListener
     */
    private $loggableListener;

    /**
     * @param LoggableListener $loggableListener
     */
    public function __construct(LoggableListener $loggableListener)
    {
        $this->loggableListener = $loggableListener;
    }

    /**
     * Méthode appelée avant tout routage
     *
     * @param Zend_Controller_Request_Abstract $request Requête HTTP
     */
    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
        $userId = $this->getLoggedInUserId();

        if ($userId) {
            $this->loggableListener->setUsername($userId);
        }
    }

    /**
     * Retourne l'id de l'utilisateur connecté
     *
     * @return int|null
     */
    public function getLoggedInUserId()
    {
        $auth = Zend_Auth::getInstance();

        if ($auth->hasIdentity()) {
            return $auth->getIdentity();
        }

        return null;
    }

}
