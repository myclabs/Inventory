<?php
/**
 * @author guillaume.querat
 * @author matthieu.napoli
 * @package Techno
 */
use Keyword\Domain\Keyword;

/**
 * Classe Meaning
 * @package Techno
 */
class Techno_Model_Meaning extends Core_Model_Entity
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
     * @var string
     */
    protected $keyword;


    /**
     * @param string $refKeyword
     * @return Techno_Model_Meaning
     */
    public static function loadByRef($refKeyword)
    {
        return self::getEntityRepository()->loadBy(['refKeyword' => $refKeyword]);
    }

    /**
     * Valide le mot-clé associé à la signification
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
     * Affecte le mot-clé
     * @param Keyword $keyword
     */
    public function setKeyword(Keyword $keyword)
    {
        $this->keyword = $keyword;
        $this->refKeyword = $keyword->getRef();

        // Update les coordonnées des cellules des familles
        if ($this->id !== null) {
            $query = new Core_Model_Query();
            $query->filter->addCondition(Techno_Model_Family_Dimension::QUERY_MEANING, $this);
            /** @var $dimensions Techno_Model_Family_Dimension[] */
            $dimensions = Techno_Model_Family_Dimension::loadList($query);
            foreach ($dimensions as $dimension) {
                $family = $dimension->getFamily();
                foreach ($family->getCells() as $cell) {
                    $cell->updateMembersHashKey();
                    $cell->save();
                }
            }
        }
    }

    /**
     * Renvoie le mot-clé
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

}
