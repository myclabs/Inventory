<?php
/**
 * @author     valentin.claras
 * @package    TEC
 * @subpackage Algo
 */

namespace TEC\Algo;

use TEC\Component\Component;
use TEC\Component\Composite;
use TEC\Component\Leaf;
use Core_Exception_UndefinedAttribute;

/**
 * Description des expressions numériques et méthodes permettant leur manipulation.
 * @package    TEC
 * @subpackage Algo
 */
class Numeric extends Algo
{
    /**
     * {@inheritDoc}
     */
    protected $mandatoryCharacters = '\+\-\*\/';


    /**
     * {@inheritDoc}
     */
    protected function getSpecificErrors($expression)
    {
        $expression = trim($expression);

        $errors = array();
        $expressionTab = str_split($expression);
        $sizeExpression = count($expressionTab);

        $hasFirstLevelSymbol = false;
        $hasSecondLevelSymbol = false;
        $nbBrackets = 0;
        $tempExpression = '';
        $expressionParts = array();
        // On parcourt chaque caracatère de l'expression pour déterminer les composant du niveau courant.
        for ($position = 0; $position < $sizeExpression; $position++) {
            $character = $expressionTab[$position];
            switch ($character) {
                case '(' :
                    $nbBrackets++;
                    $tempExpression .= $character;
                    break;
                case ')':
                    if ($nbBrackets > 0) {
                        $nbBrackets--;
                        $tempExpression .= $character;
                    } else {
                        $errors[] = __('TEC', 'syntaxError', 'closingBracketNotOpened');
                    }
                    break;
                case '+':
                case '-':
                    if ($nbBrackets > 0) {
                        $tempExpression .= $character;
                    } else {
                        if (($hasSecondLevelSymbol) && (!$hasFirstLevelSymbol)) {
                            $expressionParts = array(implode('*', $expressionParts));
                        } else if (!$hasFirstLevelSymbol) {
                            $hasFirstLevelSymbol = true;
                        }
                        $expressionParts[] = $tempExpression;
                        $tempExpression = '';
                    }
                    break;
                case '*':
                case '/':
                    if (($nbBrackets > 0) || ($hasFirstLevelSymbol)) {
                        $tempExpression .= $character;
                    } else {
                        if (!$hasSecondLevelSymbol) {
                            $hasSecondLevelSymbol = true;
                        }
                        $expressionParts[] = $tempExpression;
                        $tempExpression = '';
                    }
                    break;
                default:
                    $tempExpression .= $character;
                    break;
            }
        }
        if ($nbBrackets > 0) {
            $errors[] = __('TEC', 'syntaxError', 'openingBracketNotClosed');
            return $errors;
        }
        $expressionParts[] = $tempExpression;

        if (count($expressionParts) > 1) {
            foreach ($expressionParts as $subExpression) {
                $errors = array_merge($errors, $this->getSpecificErrors($subExpression));
            }
        } else {
            if ($expression === '') {
                $errors[] = __('TEC', 'syntaxError', 'emptyOperand', array('PART' => $expression));
            } else if (preg_match("#^\(.*\)$#s", $expression)) {
                $errors = array_merge($errors, $this->getSpecificErrors(substr($expression, 1, -1)));
            } else if (preg_match("#\s|[^\+\-\*\/]\s*\(|\)\s*[^\+\-\*\/]#", $expression)) {
                $errors[] = __('TEC', 'syntaxError', 'missingOperator', array('PART' => $expression));
            } else if (!preg_match("#^\w+$#", $expression)) {
                $errors[] = __('TEC', 'syntaxError', 'invalidOperand', array('PART' => $expression));
            }
        }

        return $errors;
    }

    /**
     * {@inheritDoc}
     */
    protected function convertNodeToString(Component $node)
    {
        $expression = '';

        if ($node instanceof  Leaf) {
            $expression .= $node->getName();
        } else {
            /** @var Composite $node */
            $addSymbol = $this->getSymbol($node, Component::MODIFIER_ADD);
            $subSymbol = $this->getSymbol($node, Component::MODIFIER_SUB);
            $addChildren = array();
            $subChildren = array();
            foreach ($node->getChildren() as $child) {
                if ($child->getModifier() === Component::MODIFIER_SUB) {
                    $subChildren[] = $child;
                } else {
                    $addChildren[] = $child;
                }
            }
            $addExpression = $this->getInsideExpression($addChildren, $addSymbol);
            if (trim($addExpression) !== '') {
                $expression .= $addExpression;
            }
            $subExpression = $this->getInsideExpression($subChildren, $addSymbol);
            if (trim($subExpression) !== '') {
                if ((trim($addExpression) !== '') && (count($subChildren) > 1)) {
                    $subExpression = ' (' . $subExpression . ')';
                }
                $expression .= $subSymbol . $subExpression;
            }
            if (($node->getParent() !== null) && ((trim($addExpression) !== '') || (trim($subExpression) !== ''))) {
                $expression = ' (' . $expression . ')';
            }
        }

        // On remplace les doubles espaces (ou plus d'espaces que 2) par un espace simple.
        $expression = preg_replace('#\s{2,}#', ' ', $expression);

        return $expression;
    }

    /**
     * Permet de récupérer le symbol d'un noeud.
     *
     * @param Composite $node
     * @param string $modifier
     *
     * @throws Core_Exception_UndefinedAttribute
     * 
     * @return string
     */
    protected function getSymbol($node, $modifier)
    {
        switch ($node->getOperator()) {
            case Composite::OPERATOR_SUM:
                if ($modifier === Component::MODIFIER_SUB) {
                    return ' - ';
                } else {
                    return ' + ';
                }
                break;
            case Composite::OPERATOR_PRODUCT:
                if ($modifier === Component::MODIFIER_SUB) {
                    return ' / ';
                } else {
                    return ' * ';
                }
                break;
            default:
                throw new Core_Exception_UndefinedAttribute('None symbol has been defined yet.');
        }
    }

    /**
     * Renvoi sous forme de string un ensemble
     *
     * @param array  $arrayNode
     * @param string $operator
     * 
     * @return string
     */
    protected function getInsideExpression($arrayNode, $operator)
    {
        $expression = '';

        foreach ($arrayNode as $node) {
            $expression .= $this->convertNodeToString($node);
            $expression .= $operator;
        }
        if (count($arrayNode) > 0) {
            $expression = substr($expression, null, -(strlen($operator)));
        }

        return $expression;
    }

    /**
     * {@inheritDoc}
     */
    protected function getNodeGraphName(Component $node)
    {
        $name = '';

        $parentNode = $node->getParent();
        if (($parentNode !== null) && ($parentNode->getOperator() === Composite::OPERATOR_PRODUCT)) {
            switch ($node->getModifier()) {
                case Component::MODIFIER_SUB:
                    $name .= __('TEC', 'tree', 'modifierProductSub');
                    break;
            }
        } else {
            switch ($node->getModifier()) {
                case Component::MODIFIER_ADD:
                    $name .= __('TEC', 'tree', 'modifierSumAdd');
                    break;
                case Component::MODIFIER_SUB:
                    $name .= __('TEC', 'tree', 'modifierSumSub');
                    break;
            }
        }
        if ($node instanceof Composite) {
            switch ($node->getOperator()) {
                case Composite::OPERATOR_SUM:
                    $name .= '<b>'.__('TEC', 'tree', 'operatorSum').'</b>';
                    break;
                case Composite::OPERATOR_PRODUCT:
                    $name .= '<b>'.__('TEC', 'tree', 'operatorProduct').'</b>';
                    break;
            }
        } else {
            /** @var Leaf $node */
            $name .= $node->getName();
        }

        return $name;
    }

}