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
class Doc_BibliographyController extends Core_Controller
{

    /**
     * Add document references to a bibliography
     * - AJAX
     * @Secure("editBibliography")
     */
    public function addAction()
    {
        /** @var $bibliography Doc_Model_Bibliography */
        $bibliography = Doc_Model_Bibliography::load($this->getParam('id'));
        $idDocuments = $this->getParam('documents');
        if (! is_array($idDocuments)) {
            throw new Core_Exception_InvalidHTTPQuery();
        }

        foreach ($idDocuments as $idDocument) {
            /** @var $document Doc_Model_Document */
            $document = Doc_Model_Document::load($idDocument);

            if ($bibliography->hasReferenceToDocument($document)) {
                throw new Core_Exception_User('Doc', 'bibliography', 'referenceToThisDocumentAlreadyExists');
            }

            $bibliography->referenceDocument($document);
        }
        $this->sendJsonResponse(null);
    }

}
