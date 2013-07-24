<?php
/**
 * @author  valentin.claras
 * @author  yoann.croizer
 * @author  hugo.charbonnier
 * @package Exec
 */

use Unit\UnitAPI;
use Unit\IncompatibleUnitsException;
use TEC\Component\Component;
use TEC\Component\Composite;
use TEC\Component\Leaf;

/**
 * classe Exec_Execution_Calc
 * @package    Exec
 * @subpackage Execution
 */
class Exec_Execution_Calc extends Exec_Execution
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
    protected $calcType;


    /**
     * Défini le type de calcul utilisé pour cet expression.
     *
     * @see self::CALC_VALUE
     * @see self::CALC_UNIT
     * @see self::CALC_UNITVALUE
     *
     * @param string $calculType
     * @throws Core_Exception_InvalidArgument
     */
    public function setCalculType($calculType)
    {
        if (!(in_array($calculType, array(self::CALC_VALUE, self::CALC_UNIT, self::CALC_UNITVALUE)))) {
            throw new Core_Exception_InvalidArgument('The calcul type must be a class constant');
        }
        $this->calcType = $calculType;
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
        switch ($this->calcType) {
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
                throw new Core_Exception_UndefinedAttribute('The calcul type must be defined first.');
        }
    }

    /**
     * Méthode récursive qui va parcourir l'arbre et vérifier les composants pour son éxécution.
     *
     * @param Component          $node
     * @param Exec_Interface_ValueProvider $valueProvider
     *
     * @return array
     */
    protected function getErrorsFromComponent(Component $node, Exec_Interface_ValueProvider $valueProvider)
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
     * Vérifie la compatibilité des unités de l'expression
     *
     * @param Exec_Interface_UnitProvider $unitProvider
     *
     * @return UnitAPI
     *
     * @throws IncompatibleUnitsException Unités incompatibles
     */
    public function checkUnitCompatibility(Exec_Interface_UnitProvider $unitProvider)
    {
        return $this->calculateUnitForComponent($this->expression->getRootNode(), $unitProvider);
    }

    /**
     * @param Composite          $node
     * @param Exec_Interface_UnitProvider  $unitProvider
     *
     * @return UnitAPI
     *
     * @throws IncompatibleUnitsException Unités incompatibles
     */
    public function calculateUnitForComponent(Composite $node,
                                              Exec_Interface_UnitProvider $unitProvider
    ) {
        $calculation = new Calc_Calculation_Unit();
        $calculation->setOperation($node->getOperator());

        foreach ($node->getChildren() as $child) {
            if ($child instanceof Leaf) {
                $unit = $unitProvider->getUnitForExecution($child->getName());
                $calculation->addComponents($unit, $child->getModifier());
            } else {
                /** @var $child Composite */
                $calculation->addComponents($this->calculateUnitForComponent($child, $unitProvider),
                                            $child->getModifier());
            }
        }

        return $calculation->calculate();
    }

    /**
     * Méthode récursive qui va parcourir l'arbre et renvoyer le résultat de son éxécution.
     *
     * @param Component          $node
     * @param Exec_Interface_ValueProvider $valueProvider
     *
     * @return mixed
     */
    protected function executeComponent(Component $node, Exec_Interface_ValueProvider $valueProvider)
    {
        if ($node instanceof Leaf) {
            $result = $valueProvider->getValueForExecution($node->getName());
        } else {
            /** @var $node Composite */
            $calcul = $this->getCalculation();
            $calcul->setOperation($node->getOperator());

            foreach ($node->getChildren() as $child) {
                $calcul->addComponents($this->executeComponent($child, $valueProvider), $child->getModifier());
            }

            $result = $calcul->calculate();
        }

        return $result;
    }

}
