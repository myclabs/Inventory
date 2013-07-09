<?php

/**
 * Classe de gestion des erreurs de requetes
 */
class ErrorController extends Core_Controller
{

    /**
     * Tableau regroupant les informations utiles de l'erreur.
     * @var array
     */
    public $error;

    /**
     * (non-PHPdoc)
     * @see Core_Controller_Error::errorAction()
     */
    public function errorAction()
    {
        $this->error = $this->getError();

        // Désactivation du l'ensemble du rendu pour n'afficher que l'erreur.
        $this->getResponse()->setBody(null, null);
        if ($this->getRequest()->isXmlHttpRequest()) {
            // Dans le cas d'une reqeste Ajax on désactive la vue.
            $this->getHelper('viewRenderer')->setNoRender();
        } else {
            // Passage de l'erreur à la vue.
            $this->view->error = $this->error;
        }

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

    /**
     * Fonction renvoyant l'erreur sous forme de tableau.
     *
     * Le tableau contient le code de l'erreur et le message.
     *
     * @return array
     */
    public function getError()
    {
        $log = Core_Error_Log::getInstance();

        // Récupération de l'erreur.
        $error = $this->getParam('error_handler');

        $errorInfos = array(
            'error' => $error,
        );

        switch ($error->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 Not found
                $log->logException($error->exception);
                $errorInfos['code'] = 404;
                $errorInfos['message'] = Core_Translate::get('Core', 'exception', 'pageNotFound');
                break;
            default:
                // 403 Forbidden
                if ($error->exception instanceof User_Exception_Forbidden) {
                    $errorInfos['code'] = 403;
                    $errorInfos['message'] = $error->exception->getMessage();
                    // 400 Bad request
                } elseif ($error->exception instanceof Core_Exception_User) {
                    $errorInfos['code'] = 400;
                    $errorInfos['message'] = $error->exception->getMessage();
                    // 404 Not found
                } elseif ($error->exception instanceof Core_Exception_NotFound) {
                    $log->logException($error->exception);
                    $errorInfos['code'] = 500;
                    $errorInfos['message'] = Core_Translate::get('Core', 'exception', 'applicationError');
                    // 500 Server error
                } else {
                    $log->logException($error->exception);
                    $errorInfos['code'] = 500;
                    $errorInfos['message'] = Core_Translate::get('Core', 'exception', 'applicationError');
                }
                break;
        }

        $this->getResponse()->setHttpResponseCode($errorInfos['code']);
        return $errorInfos;
    }

}
