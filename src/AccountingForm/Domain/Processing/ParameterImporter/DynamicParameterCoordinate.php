<?php

namespace AccountingForm\Domain\Processing\ParameterImporter;

use AccountingForm\Domain\ValueSet;

/**
 * @author matthieu.napoli
 * @author cyril.perraud
 */
class DynamicParameterCoordinate extends ParameterCoordinate
{
    /**
     * @var string
     */
    protected $expression;

    /**
     * @param string $dimensionRef
     * @param string $expression
     */
    public function __construct($dimensionRef, $expression)
    {
        parent::__construct($dimensionRef);

        $this->expression = (string) $expression;
    }

    /**
     * {@inheritdoc}
     */
    public function getMemberRef(ValueSet $values)
    {
        $expressionExecutor = ?;

        return $expressionExecutor->execute($expression, $values);
    }

    /**
     * {@inheritdoc}
     */
    public function validate()
    {
        return [];
    }
}
