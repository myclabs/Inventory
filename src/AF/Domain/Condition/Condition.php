<?php

namespace AF\Domain\Condition;

use AF\Domain\AF;
use Core_Model_Entity;
use Core_Tools;

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
     * Permet de charger une Condition élémentaire par son ref et son AF
     * @param string $ref
     * @param AF     $af
     * @return self
     */
    public static function loadByRefAndAF($ref, $af)
    {
        return self::getEntityRepository()->loadBy(['ref' => $ref, 'af' => $af]);
    }
}
