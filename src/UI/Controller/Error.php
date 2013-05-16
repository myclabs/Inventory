<?php
/**
 * Fichier de la classe Controller Error.
 *
 * @author     valentin.claras
 * @package    UI
 * @subpackage Controller
 */

/**
 * Classe de gestion des erreurs de requetes
 * @package    UI
 * @subpackage Controller
 */
abstract class UI_Controller_Error extends Core_Controller_Error
{
    /**
     * (non-PHPdoc)
     * @see Core_Controller_Error::errorAction()
     */
    public function errorAction()
    {
        parent::errorAction();

        if ($this->getRequest()->isXmlHttpRequest()) {
            // Envoie du message directement dans la réponse.
            $ajaxResponse = new stdClass();
            $ajaxResponse->message = $this->error['message'];
            // Tri des erreurs pour choisir l'image.
            $ajaxResponse->typeError = $this->getAlertType();
            // Affichage des informations complémentaires de l'erreur sous forme Json.
            $this->_helper->layout()->disableLayout();
            echo json_encode($ajaxResponse);
        } else {
            UI_Message::addMessageStatic($this->error['message'], $this->getAlertType());
        }
    }

    /**
     * Renvoie le type de message pour l'affichage.
     * @return string
     */
    protected function getAlertType()
    {
        return UI_Message::getTypeByHTTPCode($this->error['code']);
    }

}
