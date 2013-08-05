<?php

namespace Doc\Domain;

use Core_Model_Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Bibliothèque de documents
 *
 * @author matthieu.napoli
 */
class Library extends Core_Model_Entity
{

    /**
     * @var int
     */
    protected $id;

    /**
     * @var Collection|Document[]
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
     * @return Document[]
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * Vérifie si la bibliothèque possède un document.
     *
     * @param Document $document
     *
     * @return bool
     */
    public function hasDocument(Document $document)
    {
        return $this->documents->contains($document);
    }

    /**
     * Ajoute un document à la bibilothèque
     * @param Document $document
     */
    public function addDocument(Document $document)
    {
        if (!$this->hasDocument($document)) {
            $this->documents->add($document);
        }
    }

    /**
     * @param Document $document
     */
    public function removeDocument(Document $document)
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
