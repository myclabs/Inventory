<?php

namespace AF\Domain\Algorithm\Condition;

use AF\Domain\Algorithm\AlgoConfigurationError;
use AF\Domain\Algorithm\InputSet;
use Core_Exception_NotFound;
use Exec\Execution\Condition;
use Exec\Provider\ValueInterface;
use TEC\Exception\InvalidExpressionException;
use TEC\Expression;

/**
 * @author matthieu.napoli
 * @author yoann.croizer
 * @author hugo.charbonnier
 */
class ExpressionConditionAlgo extends ConditionAlgo implements ValueInterface
{
    /**
     * @var string
     */
    protected $expression;

    /**
     * {@inheritdoc}
     */
    public function execute(InputSet $inputSet)
    {
        $this->inputSet = $inputSet;

        // Construit l'arbre
        $tecExpression = new Expression($this->expression, Expression::TYPE_LOGICAL);

        $executionCalc = new Condition($tecExpression);
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
            $errors[] = new AlgoConfigurationError(__('Algo', 'configControl', 'emptyAlgorithmExpression', [
                'REF' => '$this->ref'
            ]), true);
            return $errors;
        }
        $tecExpression = new Expression($this->expression, Expression::TYPE_LOGICAL);
        $executionSelect = new Condition($tecExpression);
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
            $configError = new AlgoConfigurationError();
            $configError->isFatal(true);
            $configError->setMessage(__('Algo', 'configControl', 'noAlgorithmForOperand', [
                'REF_ALGO' => $this->ref,
                'EXPRESSION' => $this->expression,
                'REF_OPERAND' => $ref
            ]));
            $errors[] = $configError;
            return $errors;
        }

        // Vérifie qu'il s'agit d'un algo de type condition
        if (!$algo instanceof ConditionAlgo) {
            $configError = new AlgoConfigurationError();
            $configError->isFatal(true);
            $configError->setMessage(__('Algo', 'configControl', 'nonConditionOperandInConditionAlgorithm', [
                'REF_ALGO' => $this->ref,
                'EXPRESSION' => $this->expression,
                'REF_OPERAND' => $ref
            ]));
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
        $tecExpression = new Expression($this->expression, Expression::TYPE_LOGICAL);
        return $tecExpression->getAsString();
    }

    /**
     * @param string $expression
     * @throws InvalidExpressionException
     */
    public function setExpression($expression)
    {
        $tecExpression = new Expression($expression, Expression::TYPE_LOGICAL);
        $tecExpression->check();
        // Expression OK
        $this->expression = (string) $expression;
    }
}
