<?php

namespace AccountingForm\Domain\Processing;

use AccountingForm\Domain\ArrayValueSet;
use AccountingForm\Domain\ExpressionExecutor;
use AccountingForm\Domain\Value\NumericValue;
use AccountingForm\Domain\ValueSet;
use AF\Domain\Algorithm\AlgoConfigurationError;
use Unit\UnitAPI;

/**
 * Performs calculation between values.
 *
 * @author matthieu.napoli
 */
class Calculation implements ProcessingStep
{
    /**
     * @var string
     */
    protected $keyName;

    /**
     * @var string
     */
    protected $expression;

    /**
     * Si définie, force l'unité du résultat.
     * @var UnitAPI|null
     */
    protected $resultUnit;

    /**
     * @var ExpressionExecutor
     */
    protected $expressionExecutor;

    public function __construct(ExpressionExecutor $expressionExecutor, $keyName, $expression)
    {
        $this->expressionExecutor = $expressionExecutor;
        $this->keyName = $keyName;
        $this->expression = $expression;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(ValueSet $input)
    {
        try {
            /** @var NumericValue $result */
            $result = $this->expressionExecutor->execute($this->expression, $input);
        } catch (\Exception $e) {
            throw new ProcessingException($e->getMessage(), 0, $e);
        }

        // Convertit si nécessaire
        if ($this->resultUnit) {
            $result = $result->convertTo($this->resultUnit);
        }

        $output = new ArrayValueSet();
        $output->set($this->keyName, $result);

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function validate()
    {
        $errors = [];

        // Vérifie qu'on a bien une expression
        if ($this->expression == '') {
            $errors[] = new AlgoConfigurationError(
                __('Algo', 'configControl', 'emptyAlgorithmExpression', ['REF' => '$this->ref'])
            );
            return $errors;
        }

        // Vérifie l'expression
        // TODO

        // Vérifie la compatibilité des unités
        try {
            $calculationUnit = $calc->checkUnitCompatibility($this);
            if (!$calculationUnit->isEquivalent($this->getUnit())) {
                $errors[] = new AlgoConfigurationError(
                    __('Algo', 'configControl', 'operandUnitsNotCompatibleWithAlgoUnit', [
                        'REF_ALGO'        => $this->keyName,
                        'ALGO_UNIT'       => $this->getUnit(),
                        'EXPRESSION'      => $this->expression,
                        'EXPRESSION_UNIT' => $calculationUnit,
                    ])
                );
            }
        } catch (IncompatibleUnitsException $e) {
            $errors[] = new AlgoConfigurationError(
                __('Algo', 'configControl', 'incompatibleUnitsAmongOperands', [
                    'REF_ALGO'   => $this->keyName,
                    'EXPRESSION' => $this->expression
                ])
            );
        } catch (Core_Exception_NotFound $e) {
            // Problème référence de famille, dimension, etc.
        }

        return $errors;
    }

    /**
     * @return string
     */
    public function getKeyName()
    {
        return $this->keyName;
    }

    /**
     * @param string $keyName
     */
    public function setKeyName($keyName)
    {
        $this->keyName = (string) $keyName;
    }

    /**
     * @return UnitAPI|null
     */
    public function getResultUnit()
    {
        return $this->resultUnit;
    }

    /**
     * @param UnitAPI $resultUnit
     */
    public function setResultUnit(UnitAPI $resultUnit)
    {
        $this->resultUnit = $resultUnit;
    }
}
