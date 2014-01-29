<?php

namespace AF\Domain\Condition;

use AF\Domain\Component\Field;
use AF\Domain\AFConfigurationError;

/**
 * @author matthieu.napoli
 * @author thibaud.rolland
 * @author hugo.charbonnier
 * @author yoann.croizer
 */
abstract class ElementaryCondition extends Condition
{
    const RELATION_EQUAL = 1;
    const RELATION_NEQUAL = 2;
    const RELATION_GT = 3;
    const RELATION_LT = 4;
    const RELATION_GE = 5;
    const RELATION_LE = 6;
    const RELATION_CONTAINS = 7;
    const RELATION_NCONTAINS = 8;

    /**
     * @var Field
     */
    protected $field;

    /**
     * @var int
     */
    protected $relation = self::RELATION_EQUAL;

    /**
     * @param Field $field
     */
    public function setField(Field $field)
    {
        $this->field = $field;
    }

    /**
     * @return Field
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return int
     */
    public function getRelation()
    {
        return $this->relation;
    }

    /**
     * Méthode utilisée pour vérifier la configuration des conditions élémentaires.
     * @return AFConfigurationError[]
     */
    public function checkConfig()
    {
        $errors = array();
        // On vérifie que le champs de saisie associé à cette condition existe bien.
        if ($this->field === null) {
            $errors[] = new AFConfigurationError(
                "La condition '$this->ref' n'est associée à aucun champ",
                false,
                $this->getAf()
            );
        }
        return $errors;
    }
}
