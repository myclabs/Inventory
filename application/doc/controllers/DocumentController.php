<?php
/**
 * @author     thibaud.rolland
 * @author     matthieu.napoli
 * @package    Doc
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * @package Doc
 */
class Doc_DocumentController extends Core_Controller_Ajax
{

    /**
     * Donwload the file of a document
     * @Secure("viewDocument")
     */
    public function downloadAction()
    {
        /** @var $document Doc_Model_Document */
        $document = Doc_Model_Document::load($this->_getParam('id'));

        Doc_FileAdapter::downloadDocument($document);
    }

    /**
     * Popup qui affiche l'aide d'un composant
     * - AJAX
     * @Secure("viewDocument")
     */
    public function popupDescriptionAction()
    {
        $this->view->document = Doc_Model_Document::load($this->_getParam('id'));
        $this->_helper->layout()->disableLayout();
    }

}
