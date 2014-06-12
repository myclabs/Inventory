<?php

namespace AF\Domain\Condition;

use AF\Domain\AFConfigurationError;
use TEC\Expression;
use TEC\Component\Leaf;
use TEC\Exception\InvalidExpressionException;

/**
 * @author matthieu.napoli
 * @author thibaud.rolland
 * @author hugo.charbonnier
 * @author yoann.croizer
 */
class ExpressionCondition extends Condition
{
    /**
     * @var string
     */
    protected $expression;

    /**
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * Retourne toutes les sous-conditions qui apparaissent dans l'expression.
     * @return Condition[]
     */
    public function getSubConditions()
    {
        $tecExpression = new Expression($this->expression, Expression::TYPE_LOGICAL);
        $leafs = $tecExpression->getRootNode()->getAllLeafsRecursively();
        $subConditions = array_map(function (Leaf $leaf) {
            return Condition::loadByRefAndAF($leaf->getName(), $this->getAf());
        }, $leafs);
        return $subConditions;
    }

    /**
     * Définit l'expression de la condition
     * @param string $expression
     */
    public function setExpression($expression)
    {
        $tecExpression = new Expression($expression, Expression::TYPE_LOGICAL);
        $tecExpression->check();
        // Expression OK
        $this->expression = (string) $expression;
    }

    /**
     * Méthode utilisée pour vérifier la configuration des conditions de type expression.
     * @return AFConfigurationError[]
     */
    public function checkConfig()
    {
        $errors = array();
        // On vérifie que l'expression est sémantiquement correcte.
        try {
            $tecExpression = new Expression($this->expression, Expression::TYPE_LOGICAL);
            $tecExpression->check();
        } catch (InvalidExpressionException $e) {
            foreach ($e->getErrors() as $message) {
                $errors[] = new AFConfigurationError($message, true, $this->getAf());
            }
        }
        return $errors;
    }
}
