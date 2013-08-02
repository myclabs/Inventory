<?php
/**
 * @author guillaume.querat
 * @author matthieu.napoli
 * @package Techno
 * @subpackage Family
 */
use Keyword\Domain\Keyword;

/**
 * Classe Member
 * @package Techno
 * @subpackage Family
 */
class Techno_Model_Family_Member extends Core_Model_Entity
{

    use Core_Strategy_Ordered;

    /**
     * @var int
     */
    protected $id;

    /**
     * Mot-clé associé
     * @var string
     */
    protected $refKeyword;

    /**
     * Mot-clé associé (cache de l'objet)
     * @var Keyword
     */
    protected $keyword;

    /**
     * @var Techno_Model_Family_Dimension
     */
    protected $dimension;

    /**
     * Cellules associées à ce membre
     * On est obligé de déclarer cette relation pour avoir le cascade delete, sans ça
     * le cascade delete depuis la famille pose problème
     * @var Techno_Model_Family_Cell[]
     */
    protected $cells;

    /**
     * Construction d'un membre
     * @param Techno_Model_Family_Dimension $dimension
     * @param Keyword $keyword
     */
    public function __construct(Techno_Model_Family_Dimension $dimension, Keyword $keyword)
    {
        $this->keyword = $keyword;
        $this->refKeyword = $keyword->getRef();
        $this->dimension = $dimension;
        // Ajout réciproque à la dimension
        $dimension->addMember($this);
    }


    /**
     * Valide le mot-clé associé au membre
     * @return bool|string True si le mot-clé est valide, sinon retourne le mot-clé
     */
    public function validateKeyword()
    {
        try {
            Keyword::loadByRef($this->refKeyword);
        } catch (Core_Exception_NotFound $e) {
            return $this->refKeyword;
        }
        return true;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Keyword $keyword
     */
    public function setKeyword(Keyword $keyword)
    {
        $this->refKeyword = $keyword->getRef();
        $this->keyword = $keyword;

        // Update les coordonnées des cellules
        foreach ($this->cells as $cell) {
            $cell->updateMembersHashKey();
        }
    }

    /**
     * @return Keyword
     */
    public function getKeyword()
    {
        if ($this->keyword === null) {
            $this->keyword = Keyword::loadByRef($this->refKeyword);
        }
        return $this->keyword;
    }

    /**
     * @return string
     */
    public function getRef()
    {
        return $this->refKeyword;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        try {
            $keyword = $this->getKeyword();
            return $keyword->getLabel();
        } catch (Core_Exception_NotFound $e) {
            return $this->refKeyword;
        }
    }

    /**
     * @return Techno_Model_Family_Dimension
     */
    public function getDimension()
    {
        return $this->dimension;
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->getLabel();
    }

    /**
     * Fonction appelé avant un persist de l'objet (défini dans le mapper).
     */
    public function preSave()
    {
        try {
            $this->checkHasPosition();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $this->setPosition();
        }
    }

    /**
     * Fonction appelé avant un update de l'objet (défini dans le mapper).
     */
    public function preUpdate()
    {
        $this->checkHasPosition();
    }

    /**
     * Fonction appelé avant un delete de l'objet (défini dans le mapper).
     */
    public function preDelete()
    {
        $this->deletePosition();
    }

    /**
     * Fonction appelé après un load de l'objet (défini dans le mapper).
     */
    public function postLoad()
    {
        $this->updateCachePosition();
    }

    /**
     * Renvoie les valeurs du contexte pour l'objet.
     * @throws Core_Exception_InvalidArgument
     * @return array
     */
    protected function getContext()
    {
        if ($this->dimension->getId() == null) {
            throw new Core_Exception_InvalidArgument("La dimension du membre doit être persistée et flushée");
        }
        return [
            'dimension' => $this->getDimension(),
        ];
    }

}
