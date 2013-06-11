<?php
/**
 * @author     matthieu.napoli
 * @package    Doc
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * @package Doc
 */
class Doc_LibraryController extends Core_Controller
{

    /**
     * Liste des documents d'une librairie
     * @Secure("viewLibrary")
     */
    public function viewAction()
    {
        /** @var $library Doc_Model_Library */
        $library = Doc_Model_Library::load($this->getParam('id'));
        $this->view->library = $library;
    }

    /**
     * Add document references to a bibliography
     * - AJAX
     * @Secure("editLibrary")
     */
    public function addAction()
    {
        /** @var $library Doc_Model_Library */
        $library = Doc_Model_Library::load($this->getParam('id'));
        $this->view->id = $this->getParam('id');

        try {
            $adapter = new Doc_FileAdapter($library);
            $adapter->allowDocumentTypes(['document', 'text', 'image']);
            $adapter->addValidators();
            $result = $adapter->receive();
            $messages = $adapter->getMessages();
        } catch (Core_Exception_User $e) {
            $result = false;
            $messages = [$e->getMessage()];
        } catch (Exception $e) {
            /** @var Core_Error_Log $log */
            $log = $this->get('Core_Error_Log');
            $log->logException($e);
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
