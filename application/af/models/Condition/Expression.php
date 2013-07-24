<?php
/**
 * @author  matthieu.napoli
 * @author  thibaud.rolland
 * @author  hugo.charbonnier
 * @author  yoann.croizer
 * @package AF
 */

use TEC\Expression;
use TEC\Component\Component;
use TEC\Component\Composite;
use TEC\Component\Leaf;
use TEC\Exception\InvalidExpressionException;

/**
 * @package    AF
 * @subpackage Condition
 */
class AF_Model_Condition_Expression extends AF_Model_Condition
{

    /**
     * @var string
     */
    protected $expression;

    /**
     * @var Expression
     */
    protected $tecExpression;


    /**
     * {@inheritdoc}
     */
    public function getUICondition(AF_GenerationHelper $generationHelper)
    {
        $tree = $this->getTECExpression();
        // On construit l'expression en partant de la racine
        $uiCondition = $this->buildUICondition($tree->getRootNode(), $generationHelper);
        return $uiCondition;
    }

    /**
     * @return string
     */
    public function getExpression()
    {
        return $this->tecExpression->getTreeAsString();
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
        $this->tecExpression = $tecExpression;
    }

    /**
     * Get a tree created from the expression
     * @return Expression
     */
    public function getTECExpression()
    {
        return $this->tecExpression;
    }

    /**
     * Retourne toutes les feuilles (conditions élémentaires) de l'expression
     * @param Composite|null $tree Sous-arbre de l'expression, si null alors l'expression entière
     * @return AF_Model_Condition_Elementary[]
     */
    public function getElementary(Composite $tree = null)
    {
        if ($tree === null) {
            $tree = $this->getTECExpression()->getRootNode();
        }
        $elementaryList = [];
        foreach ($tree->getChildren() as $child) {
            if ($child instanceof Leaf) {
                $elementaryList[] = AF_Model_Condition_Elementary::loadByRefAndAF($child->getName(), $this->af);
            } elseif ($child instanceof Composite) {
                $elementaryList += $this->getElementary($child);
            }
        }
        return $elementaryList;
    }

    /**
     * Méthode utilisée pour vérifier la configuration des conditions de type expression.
     * @return AF_ConfigError[]
     */
    public function checkConfig()
    {
        $errors = array();
        // On vérifie que l'expression est sémantiquement correcte.
        try {
            $this->tecExpression->check();
        } catch (InvalidExpressionException $e) {
            foreach ($e->getErrors() as $message) {
                $errors[] = new AF_ConfigError($message, true, $this->getAf());
            }
        }
        return $errors;
    }

    /**
     * Construit la condition UI Expression en parcourant l'arbre de l'expression TEC
     * @param Component $currentNode
     * @param AF_GenerationHelper $generationHelper
     * @return UI_Form_Condition
     */
    private function buildUICondition(Component $currentNode, AF_GenerationHelper $generationHelper)
    {
        if ($currentNode instanceof Composite) {
            // Noeud de l'arbre : une sous-expression
            $uiCondition = new UI_Form_Condition_Expression($this->id . '_' . $currentNode->getId());
            switch ($currentNode->getOperator()) {
                case Composite::LOGICAL_AND:
                    $uiCondition->expression = UI_Form_Condition_Expression::AND_SIGN;
                    break;
                case Composite::LOGICAL_OR:
                    $uiCondition->expression = UI_Form_Condition_Expression::OR_SIGN;
                    break;
            }
            foreach ($currentNode->getChildren() as $child) {
                $uiCondition->addCondition($this->buildUICondition($child, $generationHelper));
            }
            return $uiCondition;
        }
        // Feuille de l'arbre
        /** @var $currentNode Leaf */
        $childUICondition = AF_Model_Condition::loadByRefAndAF($currentNode->getName(),
                                                               $this->getAf());
        return $generationHelper->getUICondition($childUICondition);
    }

}
