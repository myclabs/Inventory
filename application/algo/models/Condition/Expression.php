<?php
/**
 * @author  matthieu.napoli
 * @author  yoann.croizer
 * @author  hugo.charbonnier
 * @package Algo
 * @subpackage Condition
 */

/**
 * @package    Algo
 * @subpackage Condition
 */
class Algo_Model_Condition_Expression extends Algo_Model_Condition
    implements Exec_Interface_ValueProvider
{

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
     * @return bool
     */
    public function execute(Algo_Model_InputSet $inputSet)
    {
        $this->inputSet = $inputSet;
        $executionCalc = new Exec_Execution_Condition($this->tecExpression);
        return $executionCalc->executeExpression($this);
    }

    /**
     * @param string $ref
     * @return bool
     */
    public function getValueForExecution($ref)
    {
        $algo = $this->set->getAlgoByRef($ref);
        return $algo->execute($this->inputSet);
    }

    /**
     * {@inheritdoc}
     */
    public function checkConfig()
    {
        $errors = parent::checkConfig();
        if (!$this->expression) {
            $errors[] = new Algo_ConfigError(__('Algo', 'configControl', 'emptyAlgorithmExpression',
                                                ['REF' => '$this->ref']),
                                             true);
            return $errors;
        }
        $executionSelect = new Exec_Execution_Condition($this->tecExpression);
        return array_merge($errors, $executionSelect->getErrors($this));
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

        // Vérifie qu'il s'agit d'un algo de type condition
        if (!$algo instanceof Algo_Model_Condition) {
            $configError = new Algo_ConfigError();
            $configError->isFatal(true);
            $configError->setMessage(__('Algo', 'configControl', 'nonConditionOperandInConditionAlgorithm',
                ['REF_ALGO' => $this->ref, 'EXPRESSION' => $this->expression, 'REF_OPERAND' => $ref]));
            $errors[] = $configError;
            return $errors;
        }

        return $errors;
    }

    /**
     * @return string
     */
    public function getExpression()
    {
        return $this->tecExpression->getTreeAsString();
    }

    /**
     * @param string $expression
     * @throws TEC_Model_InvalidExpressionException
     */
    public function setExpression($expression)
    {
        $tecExpression = new TEC_Model_Expression();
        $tecExpression->setType(TEC_Model_Expression::TYPE_LOGICAL);
        $tecExpression->setExpression($expression);
        $tecExpression->check();
        // Expression OK
        $this->expression = (string) $expression;
        $this->tecExpression = $tecExpression;
    }

}
