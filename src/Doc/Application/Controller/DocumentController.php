<?php

use Core\Annotation\Secure;
use Doc\Application\FileAdapter;
use Doc\Domain\Document;

/**
 * @author thibaud.rolland
 * @author matthieu.napoli
 */
class Doc_DocumentController extends Core_Controller
{

    /**
     * Donwload the file of a document
     * @Secure("viewDocument")
     */
    public function downloadAction()
    {
        /** @var $document Document */
        $document = Document::load($this->getParam('id'));

        FileAdapter::downloadDocument($document);
    }

    /**
     * Popup qui affiche l'aide d'un composant
     * - AJAX
     * @Secure("viewDocument")
     */
    public function popupDescriptionAction()
    {
        $this->view->document = Document::load($this->getParam('id'));
        $this->_helper->layout()->disableLayout();
    }

}
