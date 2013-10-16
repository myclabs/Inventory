<?php

use User\ForbiddenException;

/**
 * Controleur de gestion des erreurs
 */
class ErrorController extends Core_Controller
{

    /**
     * Action appelée en cas d'erreur dans l'application
     */
    public function errorAction()
    {
        $error = $this->getParam('error_handler');
        /** @var \Exception $exception */
        $exception = $error->exception;

        switch ($error->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 Not found
                $this->logger->warning('404 Page not found: ' . $_SERVER['REQUEST_URI'], ['exception' => $exception]);
                $httpStatus = 404;
                $errorMessage = __('Core', 'exception', 'pageNotFound');
                break;
            default:
                if ($exception instanceof ForbiddenException) {
                    // 403 Forbidden
                    $this->logger->info('403 Access denied to ' . $_SERVER['REQUEST_URI']
                        . ' from ' . $_SERVER['REMOTE_ADDR']);
                    $httpStatus = 403;
                    $errorMessage = $exception->getMessage();
                } elseif ($exception instanceof Core_Exception_User) {
                    // 400 Bad request
                    $this->logger->info('400 Bad request', ['exception' => $exception]);
                    $httpStatus = 400;
                    $errorMessage = $exception->getMessage();
                } else {
                    // 500 Server error
                    $this->logger->error($exception->getMessage(), ['exception' => $exception]);
                    $httpStatus = 500;
                    $errorMessage = __('Core', 'exception', 'applicationError');
                }
                break;
        }

        $this->getResponse()->setHttpResponseCode($httpStatus);

        // Désactivation du l'ensemble du rendu pour n'afficher que l'erreur.
        $this->getResponse()->setBody(null, null);

        // Requete AJAX
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->getHelper('viewRenderer')->setNoRender();
            $this->_helper->layout()->disableLayout();

            // Envoie du message directement dans la réponse
            $ajaxResponse = new stdClass();
            $ajaxResponse->message = $errorMessage;
            $ajaxResponse->typeError = UI_Message::getTypeByHTTPCode($httpStatus);
            echo json_encode($ajaxResponse);
            return;
        }

        UI_Message::addMessageStatic($errorMessage, UI_Message::getTypeByHTTPCode($httpStatus));

        if ($this->view) {
            $this->view->assign('errorMessage', $errorMessage);
            $this->view->assign('httpStatus', $httpStatus);
            $this->view->assign('exception', $exception);
            $this->view->assign('requestParams', $error->request->getParams());
        }
    }

}
