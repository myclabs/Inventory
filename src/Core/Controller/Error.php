<?php
/**
 * @author     valentin.claras
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Controller
 */

/**
 * Classe de gestion des erreurs de requetes.
 *
 * @package    Core
 * @subpackage Controller
 */
abstract class Core_Controller_Error extends Core_Controller
{
    /**
     * Tableau regroupant les informations utiles de l'erreur.
     * @var array
     */
    public $error;


    /**
     * Méthode appelée lors d'une erreur de requête.
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
        // Récupération de l'erreur.
        $error = $this->_getParam('error_handler');

        $errorInfos = array(
            'error' => $error,
        );

        switch ($error->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 Not found
                Core_Error_Log::getInstance()->logException($error->exception);
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
                    Core_Error_Log::getInstance()->logException($error->exception);
                    $errorInfos['code'] = 404;
                    $errorInfos['message'] = Core_Translate::get('Core', 'exception', 'pageNotFound');
                // 500 Server error
                } else {
                    Core_Error_Log::getInstance()->logException($error->exception);
                    $errorInfos['code'] = 500;
                    $errorInfos['message'] = Core_Translate::get('Core', 'exception', 'applicationError');
                }
                break;
        }

        $this->getResponse()->setHttpResponseCode($errorInfos['code']);
        return $errorInfos;
    }

}
