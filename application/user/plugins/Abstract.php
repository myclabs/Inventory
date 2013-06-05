<?php
/**
 * @author     matthieu.napoli
 * @package    User
 * @subpackage Plugin
 */
use User\ForbiddenException;

/**
 * Définition des plugins pour la vérification des ACL
 *
 * @package    User
 * @subpackage Plugin
 * @uses       Zend_Controller_Plugin_Abstract
 */
abstract class User_Plugin_Abstract extends Zend_Controller_Plugin_Abstract
{

    /**
     * @var User_Service_ACL
     */
    protected $aclService;

    /**
     * Méthode appelée avant qu'une action ne soit distribuée par le distributeur.
     * Cette méthode permet un filtrage ou un proxy.
     *
     * Est utilisée pour rediriger l'utilisateur s'il n'a pas le droit d'accéder
     * à la page demandée.
     *
     * @param Zend_Controller_Request_Abstract $request Requête HTTP
     * @return void
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $this->aclService = User_Service_ACL::getInstance();

        $module = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();

        // Exception pour les pages d'erreur
        if ($controller == 'error') {
            return;
        }

        // Vérifie si l'utilisateur est connecté.
        $identity = $this->getLoggedInUser();

        if ($this->isAuthorized($identity, $module, $controller, $action, $request)) {
            // L'utilisateur est autorisé
            return;
        }

        // Si l'utilisateur n'a pas accès et qu'il n'est pas connecté : redirection sur la page de login.
        if ($identity === null) {
            $this->goLogin($request);
        } else {
            $this->goErrorForbidden($request);
        }
    }

    /**
     * Retourne l'utilisateur connecté
     *
     * @return User_Model_User|null
     */
    public function getLoggedInUser()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            return User_Model_User::load($auth->getIdentity());
        }
        return null;
    }

    /**
     * Renvoie true si l'utilisateur est autorisé à accéder à la page, renvoie false sinon
     * @param   User_Model_User|null             $user
     * @param   string                           $module
     * @param   string                           $controller
     * @param   string                           $action
     * @param   Zend_Controller_Request_Abstract $request
     * @return  bool Accès autorisé ou non
     */
    private function isAuthorized($user, $module, $controller, $action, Zend_Controller_Request_Abstract $request)
    {
        /** @var $controllerSecurityService User_Service_ControllerSecurity */
        $controllerSecurityService = User_Service_ControllerSecurity::getInstance();
        try {
            $securityRule = $controllerSecurityService->getSecurityRule($module, $controller, $action);
        } catch (Exception $e) {
            // En cas d'erreur, on loggue et on refuse l'accès.
            Core_Error_Log::getInstance()->logException($e);
            return false;
        }
        // Pas de SecurityRule => accès refusé
        if ($securityRule == null) {
            return false;
        }

        // Exception pour la règle "public"
        if ($securityRule == 'public') {
            return true;
        }

        // Vérifie qu'on a bien un utilisateur
        if ($user === null) {
            return false;
        }

        $methodName = $securityRule . 'Rule';
        try {
            return $this->$methodName($user, $request);
        } catch (Exception $e) {
            // En cas d'erreur, on loggue et on refuse l'accès.
            Core_Error_Log::getInstance()->logException($e);
            return false;
        }
    }

    /**
     * Méthode magique d'appel à une fonction
     *
     * Est appelée quand la méthode associée à un controleur n'a pas été créée dans le Helper.
     * On retourne donc false, car aucun droit n'a été spécifié pour le contrôleur demandé.
     *
     * @param  string  $nom
     * @param  array() $arguments
     * @return bool    Accès autorisé ou non
     */
    public function __call($nom, $arguments)
    {
        return false;
    }

    /**
     * Redirige vers le controleur d'erreur avec erreur d'accès interdit
     * @param Zend_Controller_Request_Abstract $request
     */
    protected function goLogin(Zend_Controller_Request_Abstract $request)
    {
        // Sauvegarde de la page voulue en tant que référence.
        $helper = new Core_View_Helper_GetUrl();
        $request->setParam('refer', urlencode($helper->getUrl()));
        // Redirige vers le login.
        $request->setModuleName('user');
        $request->setControllerName('action');
        $request->setActionName('login');
        // Affiche un message
        UI_Message::addMessageStatic(__('User', 'login', 'youAreNotConnected'), UI_Message::TYPE_ALERT);
    }

    /**
     * Redirige vers le controleur d'erreur avec erreur d'accès interdit
     * @param Zend_Controller_Request_Abstract $request
     */
    protected function goErrorForbidden(Zend_Controller_Request_Abstract $request)
    {
        // Affichage dans Firebug pour plus de clarté
        $request->setModuleName('default');
        $request->setControllerName('error');
        $request->setActionName('error');
        // Passage de l'exception ForbiddenException au controleur d'erreur
        $errorObject = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
        $errorObject->exception = new ForbiddenException();
        $errorObject->type = Zend_Controller_Plugin_ErrorHandler::EXCEPTION_OTHER;
        $errorObject->request = clone $request;
        $request->setParam('error_handler', $errorObject);
    }

    /**
     * Pages publiques des utilisateurs connectés.
     * @param User_Model_SecurityIdentity      $identity
     * @param Zend_Controller_Request_Abstract $request
     * @return bool true
     */
    public function loggedInRule(User_Model_SecurityIdentity $identity, Zend_Controller_Request_Abstract $request)
    {
        return true;
    }

}
