<?php
/**
 * @author     thibaud.rolland
 * @author     matthieu.napoli
 * @package    Doc
 * @subpackage Model
 */
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Document utilisateur
 *
 * @package    Doc
 * @subpackage Model
 */
class Doc_Model_Document extends Core_Model_Entity
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
     * @var Doc_Model_Library
     */
    protected $library;

    /**
     * Liste des bibliographies qui référencent ce document
     * @var Doc_Model_Bibliography[]|Collection
     */
    protected $referencingBibliographies;


    /**
     * @param Doc_Model_Library $library  Bibliothèque dans laquelle se trouve le document
     * @param string            $filePath Chemin d'accès complet au fichier
     * @param string|null       $name
     * @throws Core_Exception_InvalidArgument Le fichier n'existe pas
     */
    public function __construct(Doc_Model_Library $library, $filePath, $name = null)
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
     * @return Doc_Model_Library Bibliothèque dans laquelle se trouve le document
     */
    public function getLibrary()
    {
        return $this->library;
    }

    /**
     * @return Doc_Model_Bibliography[] Liste des bibliographies qui utilisent ce document
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
     * @param Doc_Model_Bibliography $bibliography
     */
    public function addReferencingBibliography(Doc_Model_Bibliography $bibliography)
    {
        if (!$this->referencingBibliographies->contains($bibliography)) {
            $this->referencingBibliographies->add($bibliography);
        }
    }

    /**
     * @param Doc_Model_Bibliography $bibliography
     */
    public function removeReferencingBibliography(Doc_Model_Bibliography $bibliography)
    {
        if ($this->referencingBibliographies->contains($bibliography)) {
            $this->referencingBibliographies->removeElement($bibliography);
        }
    }

}
