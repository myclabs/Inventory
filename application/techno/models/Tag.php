<?php
/**
 * @author guillaume.querat
 * @author matthieu.napoli
 * @package Techno
 */
use Keyword\Domain\Keyword;

/**
 * Classe Tag
 * @package Techno
 */
class Techno_Model_Tag extends Core_Model_Entity
{

    /**
     * @var int
     */
    protected $id;

    /**
     * Meaning
     * @var Techno_Model_Meaning
     */
    protected $meaning;

    /**
     * Reference de la valeur du mot-clé
     * @var string
     */
    protected $value;


    /**
     * Valide le mot-clé associé au tag
     * @return bool|string True si le mot-clé est valide, sinon retourne le mot-clé
     */
    public function validateKeyword()
    {
        try {
            Keyword::loadByRef($this->value);
        } catch (Core_Exception_NotFound $e) {
            return $this->value;
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
     * Affecte le meaning
     * @param Techno_Model_Meaning $meaning
     */
    public function setMeaning(Techno_Model_Meaning $meaning)
    {
        $this->meaning = $meaning;
    }

    /**
     * Renvoie le meaning
     * @return Techno_Model_Meaning
     */
    public function getMeaning()
    {
        return $this->meaning;
    }

    /**
     * Affecte le mot-clé
     * @param Keyword $value
     */
    public function setValue(Keyword $value)
    {
        $this->value = $value->getRef();
    }

    /**
     * Renvoie le mot clé
     * @return Keyword
     */
    public function getValue()
    {
        return Keyword::loadByRef($this->value);
    }

    /**
     * Renvoie le label de la valeur du tag
     * @return Keyword
     */
    public function getValueLabel()
    {
        try {
            $keyword = $this->getValue();
            return $keyword->getLabel();
        } catch (Core_Exception_NotFound $e) {
            return $this->value;
        }
    }

}
