<?php
/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Controller
 */

/**
 * Classe abstraite de contrôleur.
 *
 * Les droits sont vérifiés automatiquement avant qu'une action soit appelée.
 *
 * @package    Core
 * @subpackage Controller
 */
abstract class Core_Controller extends Zend_Controller_Action
{
    /**
     * Helper pour les redirections.
     *
     * @var $this->_helper->getHelper('Redirector');
     */
    protected $redirector;


    /**
     * Procédures d'initialisation pour chaque page.
     *
     * Charge les helpers
     *  à la vue et à cette instance de contrôleur.
     */
    public function init()
    {
        // Charge les helpers d'action.
        $this->redirector = $this->_helper->getHelper('Redirector');
    }

    /**
     * Envoie une réponse ajax encodée en Json.
     *
     * @param mixed $reponse N'importe quel type de variable.
     */
    public function sendJsonResponse($reponse)
    {
        // Toute cette manipulation est nécessaire pour contourner
        //  un bug de Zend Framework (les headers firebug ne sont pas envoyés sinon).
        //@see http://framework.zend.com/issues/browse/ZF-4134
        $json = $this->getHelper('Json');
        $json->suppressExit = true;
        $json->sendJson($reponse);
        Zend_Wildfire_Channel_HttpHeaders::getInstance()->flush();
    }

}
