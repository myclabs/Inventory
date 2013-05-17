<?php
/**
 * @author     thibaud.rolland
 * @author     matthieu.napoli
 * @package    Doc
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Liste des documents référencés par une bibliographie
 * @package Doc
 */
class Doc_Datagrid_BibliographyController extends UI_Controller_Datagrid
{

    /**
     * {@inheritdoc}
     * @Secure("viewBibliography")
     */
    public function getelementsAction()
    {
        /** @var $bibliography Doc_Model_Bibliography */
        $bibliography = Doc_Model_Bibliography::load($this->_getParam('id'));
        $documents = $bibliography->getReferencedDocuments();

        foreach ($documents as $document) {
            $data = [];
            $data['index'] = $document->getId();
            $data['name'] = $document->getName();
            $data['extension'] = $document->getFileExtension();
            $data['description'] = $this->cellLongText('doc/document/popup-description/id/' . $document->getId(),
                                                'doc/datagrid_bibliography/get-description/id/' . $document->getId(),
                                                __('UI', 'name', 'description'),
                                                'zoom-in');
            $data['fileSize'] = $this->cellNumber($document->getFileSize() / 1024 / 1024);
            $data['download'] = $this->cellLink('doc/document/download/id/' . $document->getId(),
                                                __('Doc', 'verb', 'download'), 'download');
            $this->addLine($data);
        }

        $this->totalElements = count($documents);
        $this->send();
    }

    /**
     * {@inheritdoc}
     * @Secure("editBibliography")
     */
    public function deleteelementAction()
    {
        /** @var $bibliography Doc_Model_Bibliography */
        $bibliography = Doc_Model_Bibliography::load($this->_getParam('id'));
        /** @var $document Doc_Model_Document */
        $document = Doc_Model_Document::load($this->delete);

        $bibliography->unreferenceDocument($document);

        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

    /**
     * Retourne le texte brut de la description
     * @Secure("viewBibliography")
     */
    public function getDescriptionAction()
    {
        /** @var $document Doc_Model_Document */
        $document = Doc_Model_Document::load($this->_getParam('id'));
        $this->data = $document->getDescription();
        $this->send();
    }

}
