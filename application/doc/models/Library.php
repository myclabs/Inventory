<?php
/**
 * @author     matthieu.napoli
 * @package    Doc
 * @subpackage Model
 */
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Bibliothèque de documents
 *
 * @package    Doc
 * @subpackage Model
 */
class Doc_Model_Library extends Core_Model_Entity
{

    /**
     * @var int
     */
    protected $id;

    /**
     * @var Collection|Doc_Model_Document[]
     */
    protected $documents;


    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->documents = new ArrayCollection();
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
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * Vérifie si la bibliothèque possède un document.
     *
     * @param Doc_Model_Document $document
     *
     * @return bool
     */
    public function hasDocument(Doc_Model_Document $document)
    {
        return $this->documents->contains($document);
    }

    /**
     * Ajoute un document à la bibilothèque
     * @param Doc_Model_Document $document
     */
    public function addDocument(Doc_Model_Document $document)
    {
        if (!$this->hasDocument($document)) {
            $this->documents->add($document);
        }
    }

    /**
     * @param Doc_Model_Document $document
     */
    public function removeDocument(Doc_Model_Document $document)
    {
        if ($this->hasDocument($document)) {
            $this->documents->removeElement($document);
            $document->delete();
        }
    }

    /**
     * Vérifie si la bibliothèque possède au moins document.
     *
     * @return bool
     */
    public function hasDocuments()
    {
        return !$this->documents->isEmpty();
    }

}
