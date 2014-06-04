<?php

namespace Exec\Execution;

use Calc_Calculation;
use Calc_Calculation_Unit;
use Calc_Calculation_UnitValue;
use Calc_Calculation_Value;
use Core_Exception_InvalidArgument;
use Core_Exception_UndefinedAttribute;
use Exec\Execution;
use Exec\Provider\UnitInterface;
use Exec\Provider\ValueInterface;
use Unit\UnitAPI;
use Unit\IncompatibleUnitsException;
use TEC\Component\Component;
use TEC\Component\Composite;
use TEC\Component\Leaf;

/**
 * Exécute une expression de calcul.
 *
 * @author valentin.claras
 */
class Calc extends Execution
{
    /**
     * Défini l'execution comme étant un calcul de valeurs.
     */
    const CALC_VALUE = 'Calc_Calculation_Value';

    /**
     * Défini l'execution comme étant un calcul d'unité.
     */
    const CALC_UNIT = 'Calc_Calculation_Unit';

    /**
     * Défini l'execution comme étant un calcul de valeurs avec unités.
     */
    const CALC_UNITVALUE = 'Calc_Calculation_UnitValue';

    /**
     * Type de calcul utilisé pour cet expression.
     *
     * @see self::CALC_VALUE
     * @see self::CALC_UNIT
     * @see self::CALC_UNITVALUE
     *
     * @var string
     */
    protected $calculationType;


    /**
     * Défini le type de calcul utilisé pour cet expression.
     *
     * @see self::CALC_VALUE
     * @see self::CALC_UNIT
     * @see self::CALC_UNITVALUE
     *
     * @param string $calculationType
     * @throws Core_Exception_InvalidArgument
     */
    public function setCalculType($calculationType)
    {
        if (!(in_array($calculationType, array(self::CALC_VALUE, self::CALC_UNIT, self::CALC_UNITVALUE)))) {
            throw new Core_Exception_InvalidArgument('The calcul type must be a class constant');
        }

        $this->calculationType = $calculationType;
    }

    /**
     * Renoie le calcul utilisé pour l'expression.
     *
     * @throws Core_Exception_UndefinedAttribute
     *
     * @return Calc_Calculation
     */
    protected function getCalculation()
    {
        switch ($this->calculationType) {
            case self::CALC_VALUE:
                return new Calc_Calculation_Value();
                break;
            case self::CALC_UNIT:
                return new Calc_Calculation_Unit();
                break;
            case self::CALC_UNITVALUE:
                return new Calc_Calculation_UnitValue();
                break;
            default:
                throw new Core_Exception_UndefinedAttribute('The calculus type must be defined');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getErrorsFromComponent(Component $node, ValueInterface $valueProvider)
    {
        $errors = [];

        if ($node instanceof Leaf) {
            $errors = array_merge($errors, $valueProvider->checkValueForExecution($node->getName()));
        } elseif ($node instanceof Composite) {
            foreach ($node->getChildren() as $child) {
                $errors = array_merge($errors, $this->getErrorsFromComponent($child, $valueProvider));
            }
        }

        return $errors;
    }

    /**
     * {@inheritdoc}
     * @throws IncompatibleUnitsException Unités incompatibles
     */
    public function checkUnitCompatibility(UnitInterface $unitProvider)
    {
        return $this->calculateUnitForComponent($this->expression->getRootNode(), $unitProvider);
    }

    /**
     * @param Composite     $node
     * @param UnitInterface $unitProvider
     *
     * @return UnitAPI
     *
     * @throws IncompatibleUnitsException Unités incompatibles
     */
    public function calculateUnitForComponent(Composite $node, UnitInterface $unitProvider)
    {
        $calculation = new Calc_Calculation_Unit();
        $calculation->setOperation($node->getOperator());

        foreach ($node->getChildren() as $child) {
            if ($child instanceof Leaf) {
                $unit = $unitProvider->getUnitForExecution($child->getName());
                $calculation->addComponents($unit, $child->getModifier());
            } else {
                /** @var $child Composite */
                $calculation->addComponents(
                    $this->calculateUnitForComponent($child, $unitProvider),
                    $child->getModifier()
                );
            }
        }

        return $calculation->calculate();
    }

    /**
     * {@inheritdoc}
     */
    protected function executeComponent(Component $node, ValueInterface $valueProvider)
    {
        if ($node instanceof Leaf) {
            $result = $valueProvider->getValueForExecution($node->getName(), ValueInterface::RESULT_NUMERIC);
        } else {
            /** @var $node Composite */
            $calculation = $this->getCalculation();
            $calculation->setOperation($node->getOperator());

            foreach ($node->getChildren() as $child) {
                $calculation->addComponents($this->executeComponent($child, $valueProvider), $child->getModifier());
            }

            $result = $calculation->calculate();
        }

        return $result;
    }
}
