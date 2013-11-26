<?php
/**
 * @author  joseph.rouffet
 * @author  matthieu.napoli
 * @package Social
 */

use Core\Annotation\Secure;
use DI\Annotation\Inject;
use User\Domain\User;

/**
 * @package Social
 */
class Social_MessageController extends Core_Controller
{

    use UI_Controller_Helper_Form;

    /**
     * @Inject
     * @var Social_Service_MessageService
     */
    private $messageService;

    /**
     * Liste des messages
     * @Secure("loggedIn")
     */
    public function listAction()
    {
    }

    /**
     * Liste des messages reçus
     * @Secure("loggedIn")
     */
    public function listInboxAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
        }
    }

    /**
     * Liste des messages envoyés
     * @Secure("loggedIn")
     */
    public function listOutboxAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
        }
    }

    /**
     * Nouveau message
     * @Secure("loggedIn")
     */
    public function newAction()
    {
        if ($this->_request->isPost()) {
            $formData = $this->getFormData('newMessage');
            $title = $formData->getValue('title');
            if (empty($title)) {
                $this->addFormError('title', __('UI', 'formValidation', 'emptyRequiredField'));
            }
            $content = $formData->getValue('content');
            if (empty($content)) {
                $this->addFormError('content', __('UI', 'formValidation', 'emptyRequiredField'));
            }
            if (! $this->hasFormError()) {
                $recipientId = $formData->getValue('recipientId');
                $recipientType = $formData->getValue('recipientType');
                if ($recipientType == 'user') {
                    $recipient = User::load($recipientId);
                } else {
                    $recipient = Social_Model_UserGroup::load($recipientId);
                }
                $author = $this->_helper->auth();
                $this->messageService->sendNewMessage($author, [$recipient], $title, $content);
                UI_Message::addMessageStatic(__('Social', 'message', 'messageSent'), UI_Message::TYPE_SUCCESS);
            }
            $this->sendFormResponse();
            return;
        }
        if ($this->hasParam('idUser')) {
            $recipient = User::load($this->getParam('idUser'));
        } elseif ($this->hasParam('idUserGroup')) {
            $recipient = Social_Model_UserGroup::load($this->getParam('idUserGroup'));
        } else {
            throw new Core_Exception_InvalidHTTPQuery();
        }
        $this->view->recipient = $recipient;
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
        }
    }

    /**
     * Détails d'un message
     * @Secure("loggedIn")
     */
    public function viewAction()
    {
        $this->view->message = Social_Model_Message::load($this->getParam('id'));
        $this->view->user = $this->_helper->auth();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
        }
    }

    /**
     * Liste des contacts
     * @Secure("loggedIn")
     */
    public function contactsAction()
    {
    }

}
