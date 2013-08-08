<?php
/**
 * @package    Core
 * @subpackage Controller
 */

/**
 * Classe de gestion des contrôleurs Ajax.
 *
 * @package    Core
 * @subpackage Controller
 */
abstract class Core_Controller_Ajax extends Core_Controller
{
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
        /** @var Zend_Controller_Action_Helper_Json $json */
        $json = $this->getHelper('Json');
        $json->suppressExit = true;
        $json->sendJson($reponse);
        Zend_Wildfire_Channel_HttpHeaders::getInstance()->flush();
    }

}
