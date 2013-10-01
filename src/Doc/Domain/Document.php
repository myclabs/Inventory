<?php

namespace Doc\Domain;

use Core_Exception_InvalidArgument;
use Core_Model_Entity;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Exception;

/**
 * Document utilisateur
 *
 * @author thibaud.rolland
 * @author matthieu.napoli
 */
class Document extends Core_Model_Entity
{

    const QUERY_NAME = 'name';
    const QUERY_LIBRARY = 'library';

    /**
     * @var int
     */
    protected $id;

    /**
     * Nom du document
     * @var string
     */
    protected $name;

    /**
     * Description du document
     * @var string
     */
    protected $description;

    /**
     * Date de création du document
     * @var DateTime
     */
    protected $creationDate;

    /**
     * Chemin d'accès complet du fichier
     * @var string
     */
    protected $filePath;

    /**
     * Bibliothèque dans laquelle se trouve le document
     * @var Library
     */
    protected $library;

    /**
     * Liste des bibliographies qui référencent ce document
     * @var Bibliography[]|Collection
     */
    protected $referencingBibliographies;


    /**
     * @param Library $library  Bibliothèque dans laquelle se trouve le document
     * @param string            $filePath Chemin d'accès complet au fichier
     * @param string|null       $name
     * @throws Core_Exception_InvalidArgument Le fichier n'existe pas
     */
    public function __construct(Library $library, $filePath, $name = null)
    {
        $this->setFilePath($filePath);
        $this->referencingBibliographies = new ArrayCollection();
        $this->library = $library;
        // Nom par défaut : nom du fichier
        if ($name === null) {
            $this->setName($this->getFileName());
        }
        $this->creationDate = new DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $label
     */
    public function setName($label)
    {
        $this->name = (string) $label;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = (string) $description;
    }

    /**
     * @return DateTime Date d'ajout
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param string $filePath
     * @throws Core_Exception_InvalidArgument Le fichier n'existe pas
     */
    public function setFilePath($filePath)
    {
        // Vérifie que le fichier existe
        if (!file_exists((string) $filePath)) {
            throw new Core_Exception_InvalidArgument("File doesn't exist: '$filePath'");
        }

        $this->filePath = (string) $filePath;
    }

    /**
     * @return string Chemin d'accès vers le fichier
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @return string Nom du fichier, sans son extension
     */
    public function getFileName()
    {
        return pathinfo($this->filePath, PATHINFO_FILENAME);
    }

    /**
     * @return string Extension du fichier
     */
    public function getFileExtension()
    {
        return pathinfo($this->filePath, PATHINFO_EXTENSION);
    }

    /**
     * @return int Taille du fichier en octets
     */
    public function getFileSize()
    {
        try {
            return filesize($this->filePath);
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * @return Library Bibliothèque dans laquelle se trouve le document
     */
    public function getLibrary()
    {
        return $this->library;
    }

    /**
     * @return Bibliography[] Liste des bibliographies qui utilisent ce document
     */
    public function getReferencingBibliographies()
    {
        return $this->referencingBibliographies->toArray();
    }

    /**
     * @return int Nombre de bibliographies qui utilisent ce document
     */
    public function getReferencingBibliographiesCount()
    {
        return $this->referencingBibliographies->count();
    }

    /**
     * @param Bibliography $bibliography
     */
    public function addReferencingBibliography(Bibliography $bibliography)
    {
        if (!$this->referencingBibliographies->contains($bibliography)) {
            $this->referencingBibliographies->add($bibliography);
        }
    }

    /**
     * @param Bibliography $bibliography
     */
    public function removeReferencingBibliography(Bibliography $bibliography)
    {
        if ($this->referencingBibliographies->contains($bibliography)) {
            $this->referencingBibliographies->removeElement($bibliography);
        }
    }

}
