<?php

namespace Techno\Domain;

use Core_Model_Entity;
use Keyword\Application\Service\KeywordDTO;
use Techno\Domain\Meaning;

/**
 * @author guillaume.querat
 * @author matthieu.napoli
 */
class Tag extends Core_Model_Entity
{
    /**
     * @var int
     */
    protected $id;

    /**
     * Meaning
     * @var Meaning
     */
    protected $meaning;

    /**
     * Reference de la valeur du mot-clé
     * @var KeywordDTO
     */
    protected $value;


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Affecte le meaning
     * @param Meaning $meaning
     */
    public function setMeaning(Meaning $meaning)
    {
        $this->meaning = $meaning;
    }

    /**
     * Renvoie le meaning
     * @return Meaning
     */
    public function getMeaning()
    {
        return $this->meaning;
    }

    /**
     * Affecte le mot-clé
     * @param KeywordDTO $value
     */
    public function setValue(KeywordDTO $value)
    {
        $this->value = $value;
    }

    /**
     * Renvoie le mot clé
     * @return KeywordDTO
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Renvoie le label de la valeur du tag
     * @return KeywordDTO
     */
    public function getValueLabel()
    {
        return $this->getValue()->getLabel();
    }
}
