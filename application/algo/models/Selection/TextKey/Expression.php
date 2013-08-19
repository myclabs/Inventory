<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @author  yoann.croizer
 * @package Algo
 */

use Exec\Execution\Select;
use Exec\Provider\ValueInterface;
use TEC\Exception\InvalidExpressionException;
use TEC\Expression;

/**
 * @package    Algo
 * @subpackage Keyword
 */
class Algo_Model_Selection_TextKey_Expression extends Algo_Model_Selection_TextKey
    implements ValueInterface
{

    /**
     * @var string
     */
    protected $expression;

    /**
     * {@inheritdoc}
     */
    public function execute(Algo_Model_InputSet $inputSet)
    {
        $this->inputSet = $inputSet;

        // Construit l'arbre
        $tecExpression = new Expression($this->expression, Expression::TYPE_SELECT);

        $executionSelect = new Select($tecExpression);
        return $executionSelect->executeExpression($this);
    }

    /**
     * {@inheritdoc}
     */
    public function getValueForExecution($ref)
    {
        try {
            // Si l'opérande est le ref d'un algo, alors on renvoie le résultat de cet algo
            $algo = $this->getSet()->getAlgoByRef($ref);
            return $algo->execute($this->inputSet);
        } catch (Core_Exception_NotFound $e) {
            // Sinon on renvoie le ref
            return $ref;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function checkConfig()
    {
        $errors = parent::checkConfig();

        $tecExpression = new Expression($this->expression, Expression::TYPE_SELECT);
        $executionSelect = new Select($tecExpression);

        return array_merge($errors, $executionSelect->getErrors($this));
    }

    /**
     * {@inheritdoc}
     */
    public function checkValueForExecution($ref)
    {
        $errors = [];

        try {
            // Si l'opérande est le ref d'un algo, on vérifie que c'est bien un algo de sélection
            $algo = $this->getSet()->getAlgoByRef($ref);
            if (!$algo instanceof Algo_Model_Selection_TextKey) {
                $configError = new Algo_ConfigError();
                $configError->isFatal(true);
                $configError->setMessage(__('Algo', 'configControl', 'nonSelectOperandInSelectAlgorithm',
                        ['REF_ALGO' => $this->ref, 'EXPRESSION' => $this->expression, 'REF_OPERAND' => $ref]));
                $errors[] = $configError;
            }
        } catch (Core_Exception_NotFound $e) {
            // L'opérande n'est pas le ref d'un algo
        }

        return $errors;
    }

    /**
     * @return string
     */
    public function getExpression()
    {
        $tecExpression = new Expression($this->expression, Expression::TYPE_SELECT);
        return $tecExpression->getAsString();
    }

    /**
     * @param string $expression
     * @throws InvalidExpressionException
     */
    public function setExpression($expression)
    {
        $tecExpression = new Expression($expression, Expression::TYPE_SELECT);
        $tecExpression->check();
        // Expression OK
        $this->expression = (string) $expression;
    }

}
