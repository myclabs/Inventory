<?php

use Core\Annotation\Secure;
use Doc\Application\FileAdapter;
use Doc\Domain\Library;

/**
 * @author matthieu.napoli
 */
class Doc_LibraryController extends Core_Controller
{

    /**
     * Liste des documents d'une librairie
     * @Secure("viewLibrary")
     */
    public function viewAction()
    {
        /** @var $library Library */
        $library = Library::load($this->getParam('id'));
        $this->view->library = $library;
    }

    /**
     * Add document references to a bibliography
     * - AJAX
     * @Secure("editLibrary")
     */
    public function addAction()
    {
        /** @var $library Library */
        $library = Library::load($this->getParam('id'));
        $this->view->id = $this->getParam('id');

        try {
            $adapter = new FileAdapter($library);
            $adapter->allowDocumentTypes(['document', 'text', 'image']);
            $adapter->addValidators();
            $result = $adapter->receive();
            $messages = $adapter->getMessages();
        } catch (Core_Exception_User $e) {
            $result = false;
            $messages = [$e->getMessage()];
        } catch (Exception $e) {
            Core_Error_Log::getInstance()->logException($e);
            $result = false;
            $messages = [__('Core', 'exception', 'applicationError')];
        }

        if (!$result) {
            $this->view->success = false;
            $this->view->message = implode("\n", $messages);
        } else {
            $this->view->success = true;
        }

        $this->_helper->layout->disableLayout();
    }

}
