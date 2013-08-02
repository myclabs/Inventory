<?php

use Core\Annotation\Secure;
use Doc\Domain\Bibliography;
use Doc\Domain\Document;

/**
 * @author thibaud.rolland
 * @author matthieu.napoli
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
        /** @var $bibliography Bibliography */
        $bibliography = Bibliography::load($this->getParam('id'));
        $idDocuments = $this->getParam('documents');
        if (! is_array($idDocuments)) {
            throw new Core_Exception_InvalidHTTPQuery();
        }

        foreach ($idDocuments as $idDocument) {
            /** @var $document Document */
            $document = Document::load($idDocument);

            if ($bibliography->hasReferenceToDocument($document)) {
                throw new Core_Exception_User('Doc', 'bibliography', 'referenceToThisDocumentAlreadyExists');
            }

            $bibliography->referenceDocument($document);
        }
        $this->sendJsonResponse(null);
    }

}
