<?php

namespace Doc\Domain;

use Core_Model_Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Une bibiliographie est une liste de références vers des documents
 *
 * @author matthieu.napoli
 */
class Bibliography extends Core_Model_Entity
{

    /**
     * @var int
     */
    protected $id;

    /**
     * Documents référencés par la bibliographie
     * @var Collection|Document[]
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
     * @return Document[]
     */
    public function getReferencedDocuments()
    {
        return $this->referencedDocuments->toArray();
    }

    /**
     * Ajoute un lien vers un document
     * @param Document $document
     */
    public function referenceDocument(Document $document)
    {
        if (!$this->referencedDocuments->contains($document)) {
            $this->referencedDocuments->add($document);
            $document->addReferencingBibliography($this);
        }
    }

    /**
     * @param Document $document
     * @return boolean
     */
    public function hasReferenceToDocument(Document $document)
    {
        return $this->referencedDocuments->contains($document);
    }

    /**
     * Supprime un lien vers un document
     * @param Document $document
     */
    public function unreferenceDocument(Document $document)
    {
        if ($this->referencedDocuments->contains($document)) {
            $this->referencedDocuments->removeElement($document);
            $document->removeReferencingBibliography($this);
        }
    }

}
