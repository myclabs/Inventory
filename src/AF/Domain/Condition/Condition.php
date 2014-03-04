<?php

namespace AF\Domain\Condition;

use AF\Domain\AF;
use AF\Domain\AFGenerationHelper;
use Core_Model_Entity;
use Core_Tools;
use UI_Form_Condition;

/**
 * @author matthieu.napoli
 * @author thibaud.rolland
 * @author hugo.charbonnier
 * @author yoann.croizer
 */
abstract class Condition extends Core_Model_Entity
{
    const QUERY_AF = 'af';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $ref;

    /**
     * Form where the condition is effective.
     * @var AF
     */
    protected $af;


    /**
     * Génère une condition UI
     * @param AFGenerationHelper $generationHelper
     * @return UI_Form_Condition
     */
    abstract public function getUICondition(AFGenerationHelper $generationHelper);

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return AF
     */
    public function getAf()
    {
        return $this->af;
    }

    /**
     * @param AF $af
     */
    public function setAf(AF $af)
    {
        if ($this->af !== $af) {
            $this->af = $af;
            $af->addCondition($this);
        }
    }

    /**
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @param string $ref
     */
    public function setRef($ref)
    {
        Core_Tools::checkRef($ref);
        $this->ref = (string) $ref;
    }

    /**
     * Retourne la condition correspondant à la référence.
     * @param string $ref
     * @return self
     */
    public static function loadByRef($ref)
    {
        return self::getEntityRepository()->loadBy(array('ref' => $ref));
    }

    /**
     * Permet de charger une Condition élémentaire par son ref et son AF
     * @param string $ref
     * @param AF     $af
     * @return self
     */
    public static function loadByRefAndAF($ref, $af)
    {
        return self::getEntityRepository()->loadBy(array('ref' => $ref, 'af' => $af));
    }
}
