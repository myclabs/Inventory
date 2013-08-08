<?php
/**
 * @author     thibaud.rolland
 * @author     matthieu.napoli
 * @package    Doc
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Liste des documents d'une bibliothèque
 * @package Doc
 */
class Doc_Datagrid_LibraryController extends UI_Controller_Datagrid
{

    /**
     * {@inheritdoc}
     * @Secure("viewLibrary")
     */
    public function getelementsAction()
    {
        /** @var $library Doc_Model_Library */
        $library = Doc_Model_Library::load($this->getParam('id'));

        $this->request->filter->addCondition(Doc_Model_Document::QUERY_LIBRARY, $library);
        $this->request->order->addOrder(Doc_Model_Document::QUERY_NAME);
        /** @var $documents Doc_Model_Document[] */
        $documents = Doc_Model_Document::loadList($this->request);
        $this->totalElements = Doc_Model_Document::countTotal($this->request);

        foreach ($documents as $document) {
            $data = [];
            $data['index'] = $document->getId();
            $data['name'] = $document->getName();
            $data['extension'] = $document->getFileExtension();
            $data['description'] = $this->cellLongText('doc/document/popup-description/id/' . $document->getId(),
                                                       'doc/datagrid_library/get-description/id/' . $document->getId(),
                                                       __('UI', 'name', 'description'),
                                                       'zoom-in');
            $data['fileSize'] = $this->cellNumber($document->getFileSize() / 1024 / 1024);
            $data['referenceCount'] = $this->cellNumber($document->getReferencingBibliographiesCount());
            $data['download'] = $this->cellLink('doc/document/download/id/' . $document->getId(),
                                                __('Doc', 'verb', 'download'), 'download');
            $this->addLine($data);
        }

        $this->send();
    }

    /**
     * {@inheritdoc}
     * @Secure("editLibrary")
     */
    public function updateelementAction()
    {
        /** @var $document Doc_Model_Document */
        $document = Doc_Model_Document::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'name':
                $document->setName($newValue);
                $this->data = $document->getName();
                break;
            case 'description':
                $document->setDescription($newValue);
                $this->data = $this->cellLongText('doc/document/popup-description/id/' . $document->getId(),
                                                  'doc/datagrid_library/get-description/id/' . $document->getId(),
                                                  __('UI', 'name', 'description'),
                                                  'zoom-in');
                break;
        }
        $document->save();
        $this->message = __('UI', 'message', 'updated');
        $this->send();
    }

    /**
     * {@inheritdoc}
     * @Secure("editLibrary")
     */
    public function deleteelementAction()
    {
        /** @var $document Doc_Model_Document */
        $document = Doc_Model_Document::load($this->delete);

        Doc_FileAdapter::deleteDocumentFile($document);
        $document->delete();
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

    /**
     * Retourne le texte brut de la description
     * @Secure("viewLibrary")
     */
    public function getDescriptionAction()
    {
        /** @var $document Doc_Model_Document */
        $document = Doc_Model_Document::load($this->getParam('id'));
        $this->data = (string) $document->getDescription();
        $this->send();
    }

}
