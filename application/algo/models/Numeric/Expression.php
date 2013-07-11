<?php
/**
 * @author  hmatthieu.napoli
 * @author  hugo.charbonnier
 * @author  yoann.croizer
 * @package Algo
 */
use Unit\IncompatibleUnitsException;
use Unit\UnitAPI;

/**
 * @package    Algo
 * @subpackage Numeric
 */
class Algo_Model_Numeric_Expression extends Algo_Model_Numeric
    implements Exec_Interface_ValueProvider, Exec_Interface_UnitProvider
{

    /**
     * Unité pour contrôle automatique de la cohérence des unités.
     * @var UnitAPI
     */
    protected $unit;

    /**
     * @var string
     */
    protected $expression;

    /**
     * @var TEC_Model_Expression
     */
    protected $tecExpression;

    /**
     * Exécution de l'algorithme
     * @param Algo_Model_InputSet $inputSet
     * @return Calc_UnitValue
     */
    public function execute(Algo_Model_InputSet $inputSet)
    {
        $this->inputSet = $inputSet;
        $calc = new Exec_Execution_Calc($this->tecExpression);
        $calc->setCalculType(Exec_Execution_Calc::CALC_UNITVALUE);
        /** @var $result Calc_UnitValue */
        $result = $calc->executeExpression($this);

        // Convertit à l'unité de l'algo
        $convertedValue = $result->getDigitalValue() / $result->getUnit()->getConversionFactor($this->getUnit());

        return new Calc_UnitValue($this->getUnit(), $convertedValue, $result->getRelativeUncertainty());
    }

    /**
     * {@inheritdoc}
     *
     * Callback utilisée par Exec pour récupérer la valeur de chaque composant de l'expression
     * @param string $ref Ref de l'algo utilisé dans l'expression
     * @return Calc_UnitValue
     */
    public function getValueForExecution($ref)
    {
        $algo = $this->getSet()->getAlgoByRef($ref);
        return $algo->execute($this->inputSet);
    }

    /**
     * {@inheritdoc}
     *
     * Callback utilisée pour la vérification de la compatibilité des unités dans Exec
     */
    public function getUnitForExecution($ref)
    {
        /** @var $algo Algo_Model_Numeric */
        $algo = $this->getSet()->getAlgoByRef($ref);
        return $algo->getUnit();
    }

    /**
     * Méthode utilisée au niveau de AF pour vérifier la configuration des algorithmes
     * @return Algo_ConfigError[]
     */
    public function checkConfig()
    {
        $errors = parent::checkConfig();
        // Vérifie qu'on a bien une expression
        if (!$this->expression) {
            $configError = new Algo_ConfigError();
            $configError->isFatal(true);
            $configError->setMessage(__('Algo', 'configControl', 'emptyAlgorithmExpression', ['REF' => '$this->ref']),
                                     true);
            $errors[] = $configError;
            return $errors;
        }

        // Vérifie chaque composant de l'expression
        $calc = new Exec_Execution_Calc($this->tecExpression);
        $errors = array_merge($errors, $calc->getErrors($this));

        // Vérifie la compatibilité des unités
        try {
            $calculationUnit = $calc->checkUnitCompatibility($this);
            if (!$calculationUnit->isEquivalent($this->getUnit())) {
                $errors[] = new Algo_ConfigError(
                    __('Algo', 'configControl', 'operandUnitsNotCompatibleWithAlgoUnit',
                       [
                       'REF_ALGO'   => $this->ref,
                       'ALGO_UNIT'   => $this->getUnit(),
                       'EXPRESSION' => $this->expression,
                       'EXPRESSION_UNIT' => $calculationUnit,
                       ]
                    ),
                    true
                );
            }
        } catch (IncompatibleUnitsException $e) {
            $errors[] = new Algo_ConfigError(__('Algo', 'configControl', 'incompatibleUnitsAmongOperands',
                                                [
                                                    'REF_ALGO' => $this->ref,
                                                    'EXPRESSION' => $this->expression
                                                ]),
                                             true);
        } catch (Core_Exception_NotFound $e) {
            // Problème référence de famille, dimension, etc.
        }

        return $errors;
    }

    /**
     * {@inheritdoc}
     */
    public function checkValueForExecution($ref)
    {
        $errors = [];

        // Teste si le ref correspond à un algo existant
        try {
            $algo = $this->set->getAlgoByRef($ref);
        } catch (Core_Exception_NotFound $e) {
            $configError = new Algo_ConfigError();
            $configError->isFatal(true);
            $configError->setMessage(__('Algo', 'configControl', 'noAlgorithmForOperand',
                ['REF_ALGO' => $this->ref, 'EXPRESSION' => $this->expression, 'REF_OPERAND' => $ref]));
            $errors[] = $configError;
            return $errors;
        }

        // Vérifie qu'il s'agit d'un algo numérique
        if (!$algo instanceof Algo_Model_Numeric) {
            $configError = new Algo_ConfigError();
            $configError->isFatal(true);
            $configError->setMessage(__('Algo', 'configControl', 'nonNumericOperandInNumericAlgorithm',
                ['REF_ALGO' => $this->ref, 'EXPRESSION' => $this->expression, 'REF_OPERAND' => $ref]));
            $errors[] = $configError;
            return $errors;
        }

        return $errors;
    }

    /**
     * @return UnitAPI
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param UnitAPI $unit
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
    }

    /**
     * @return string
     */
    public function getExpression()
    {
        return $this->tecExpression->getExpression();
    }

    /**
     * @param string $expression
     * @throws TEC_Model_InvalidExpressionException
     */
    public function setExpression($expression)
    {
        $tecExpression = new TEC_Model_Expression();
        $tecExpression->setType(TEC_Model_Expression::TYPE_NUMERIC);
        $tecExpression->setExpression($expression);
        $tecExpression->check();
        // Expression OK
        $this->expression = (string) $expression;
        $this->tecExpression = $tecExpression;
    }

    /**
     * @return Algo_Model_Algo[]
     */
    public function getSubAlgos()
    {
        $leafs = $this->tecExpression->getRootNode()->getAllLeafsRecursively();
        $subAlgos = [];
        foreach ($leafs as $leaf) {
            $subAlgoRef = $leaf->getName();
            $subAlgos[] = Algo_Model_Algo::loadByRef($this->set, $subAlgoRef);
        }
        return $subAlgos;
    }

}
