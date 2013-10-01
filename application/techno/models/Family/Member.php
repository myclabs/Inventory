<?php
/**
 * @author guillaume.querat
 * @author matthieu.napoli
 * @package Techno
 * @subpackage Family
 */
use Keyword\Application\Service\KeywordDTO;

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
     * @var KeywordDTO
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
     * @param KeywordDTO $keyword
     */
    public function __construct(Techno_Model_Family_Dimension $dimension, KeywordDTO $keyword)
    {
        $this->keyword = $keyword;
        $this->dimension = $dimension;
        // Ajout réciproque à la dimension
        $dimension->addMember($this);
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param KeywordDTO $keyword
     */
    public function setKeyword(KeywordDTO $keyword)
    {
        $this->keyword = $keyword;

        // Update les coordonnées des cellules
        foreach ($this->cells as $cell) {
            $cell->updateMembersHashKey();
        }
    }

    /**
     * @return KeywordDTO
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * @return string
     */
    public function getRef()
    {
        return $this->keyword->getRef();
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->keyword->getLabel();
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
     * Fonction appelée avant un persist de l'objet (défini dans le mapper).
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
     * Fonction appelée avant un update de l'objet (défini dans le mapper).
     */
    public function preUpdate()
    {
        $this->checkHasPosition();
    }

    /**
     * Fonction appelée avant un delete de l'objet (défini dans le mapper).
     */
    public function preDelete()
    {
        $this->deletePosition();
    }

    /**
     * Fonction appelée après un load de l'objet (défini dans le mapper).
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
