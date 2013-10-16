<?php

namespace Techno\Domain;

use Core_Exception_UndefinedAttribute;
use Core_Model_Entity;
use Core_Model_Query;
use Core_Strategy_Ordered;
use Keyword\Application\Service\KeywordDTO;
use Techno\Domain\Family\Dimension;

/**
 * @author guillaume.querat
 * @author matthieu.napoli
 */
class Meaning extends Core_Model_Entity
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
     * @param string $refKeyword
     * @return Meaning
     */
    public static function loadByRef($refKeyword)
    {
        return self::getEntityRepository()->loadBy(['keyword' => $refKeyword]);
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
     * @param KeywordDTO $keyword
     */
    public function setKeyword(KeywordDTO $keyword)
    {
        $this->keyword = $keyword;

        // Update les coordonnées des cellules des familles
        if ($this->id !== null) {
            $query = new Core_Model_Query();
            $query->filter->addCondition(Dimension::QUERY_MEANING, $this);
            /** @var $dimensions \Techno\Domain\Family\Dimension[] */
            $dimensions = Dimension::loadList($query);
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
}
