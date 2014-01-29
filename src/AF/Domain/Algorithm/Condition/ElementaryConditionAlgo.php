<?php

namespace AF\Domain\Algorithm\Condition;

use AF\Domain\Algorithm\AlgoConfigurationError;

/**
 * @author matthieu.napoli
 * @author yoann.croizer
 * @author hugo.charbonnier
 */
abstract class ElementaryConditionAlgo extends ConditionAlgo
{
    /**
     * Filtre sur l'élément cible.
     */
    const QUERY_INPUT_REF = 'inputRef';

    const RELATION_EQUAL = 1;
    const RELATION_NOTEQUAL = 2;
    const RELATION_GT = 3;
    const RELATION_LT = 4;
    const RELATION_GE = 5;
    const RELATION_LE = 6;
    const RELATION_CONTAINS = 7;
    const RELATION_NOTCONTAINS = 8;

    /**
     * @var int
     */
    protected $relation;

    /**
     * @var string
     */
    protected $inputRef;

    /**
     * {@inheritdoc}
     */
    public function checkConfig()
    {
        $errors = parent::checkConfig();
        if ((!isset($this->inputRef)) || ($this->inputRef === '')) {
            $configError = new AlgoConfigurationError();
            $configError->isFatal(true);
            $configError->setMessage("L'algorithme '" . $this->ref . "' n'est associé à aucun champ.");
            $errors[] = $configError;
        }
        if ($this->relation < 1 || $this->relation > 6) {
            $configError = new AlgoConfigurationError();
            $configError->isFatal(true);
            $configError->setMessage(__('Algo', 'configControl', 'noRelationForElementaryCondition', [
                'REF_ALGO' => $this->ref
            ]));
            $errors[] = $configError;
        }
        return $errors;
    }

    /**
     * @return int
     */
    public function getRelation()
    {
        return $this->relation;
    }

    /**
     * @param int $relation
     */
    public function setRelation($relation)
    {
        $this->relation = $relation;
    }

    /**
     * @return string
     */
    public function getInputRef()
    {
        return $this->inputRef;
    }

    /**
     * @param string $inputRef
     */
    public function setInputRef($inputRef)
    {
        $this->inputRef = $inputRef;
    }
}
