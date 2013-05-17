<?php
/**
 * @author     matthieu.napoli
 * @package    Doc
 * @subpackage Model
 */

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Une bibiliographie est une liste de références vers des documents
 *
 * @package    Doc
 * @subpackage Model
 */
class Doc_Model_Bibliography extends Core_Model_Entity
{

    /**
     * @var int
     */
    protected $id;

    /**
     * Document référencés par la bibliographie
     * @var Collection|Doc_Model_Document[]
     */
    protected $referencedDocuments;


    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->referencedDocuments = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Doc_Model_Document[]
     */
    public function getReferencedDocuments()
    {
        return $this->referencedDocuments->toArray();
    }

    /**
     * Ajoute un lien vers un document
     * @param Doc_Model_Document $document
     */
    public function referenceDocument(Doc_Model_Document $document)
    {
        if (!$this->referencedDocuments->contains($document)) {
            $this->referencedDocuments->add($document);
            $document->addReferencingBibliography($this);
        }
    }

    /**
     * @param Doc_Model_Document $document
     * @return boolean
     */
    public function hasReferenceToDocument(Doc_Model_Document $document)
    {
        return $this->referencedDocuments->contains($document);
    }

    /**
     * Supprime un lien vers un document
     * @param Doc_Model_Document $document
     */
    public function unreferenceDocument(Doc_Model_Document $document)
    {
        if ($this->referencedDocuments->contains($document)) {
            $this->referencedDocuments->removeElement($document);
            $document->removeReferencingBibliography($this);
        }
    }

}
