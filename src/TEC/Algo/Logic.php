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
 * Description des expressions logiques et méthodes permettant leur manipulation.
 * @package TEC
 * @subpackage Algo
 */
class Logic extends Algo
{
    /**
     * {@inheritDoc}
     */
    protected $mandatoryCharacters = '\&\|';


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
                case '|':
                    if ($nbBrackets > 0) {
                        $tempExpression .= $character;
                    } else {
                        if (($hasSecondLevelSymbol) && (!$hasFirstLevelSymbol)) {
                            $expressionParts = array(implode('|', $expressionParts));
                        } else if (!$hasFirstLevelSymbol) {
                            $hasFirstLevelSymbol = true;
                        }
                        $expressionParts[] = $tempExpression;
                        $tempExpression = '';
                    }
                    break;
                case '&' :
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
            } else if (preg_match("#^\!?\(.*\)$#s", $expression)) {
                if (preg_match("#^\!#", $expression)) {
                    $errors = array_merge($errors, $this->getSpecificErrors(substr($expression, 2, -1)));
                } else {
                    $errors = array_merge($errors, $this->getSpecificErrors(substr($expression, 1, -1)));
                }
            } else if (preg_match("#\s|[^\&\|]\s*\(|\)\s*[^\&\|]#", $expression)) {
                $errors[] = __('TEC', 'syntaxError', 'missingOperator', array('PART' => $expression));
            } else if (!preg_match("#^\!?\w+$#", $expression)) {
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
            $symbol = $this->getSymbol($node);
            foreach ($node->getChildren() as $child) {
                $expression .= $this->convertNodeToString($child);
                $expression .= $symbol;
            }
            $expression = substr($expression, null, -3);
            if ($node->getParent() !== null) {
                $expression = '(' . $expression . ')';
            }
        }
        if ($node->getModifier() == Component::MODIFIER_NOT) {
            $expression = '!'.$expression;
        }

        return $expression;
    }

    /**
     * Permet de récupérer le symbol d'un noeud.
     *
     * @param Composite $node
     *
     * @throws Core_Exception_UndefinedAttribute
     *
     * @return string
     */
    protected function getSymbol(Composite $node)
    {
        switch ($node->getOperator()) {
            case Composite::LOGICAL_AND:
                return ' & ';
                break;
            case Composite::LOGICAL_OR:
                return ' | ';
                break;
            default:
                throw new Core_Exception_UndefinedAttribute('None symbol has been defined yet.');
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getNodeGraphName(Component $node)
    {
        $name = '';

        switch ($node->getModifier()) {
            case Component::MODIFIER_NOT:
                $name .= __('TEC', 'tree', 'modifierLogicalNot');
                break;
        }
        if ($node instanceof Composite) {
            switch ($node->getOperator()) {
                case Composite::LOGICAL_OR:
                    $name .= '<b>'.__('TEC', 'tree', 'operatorOr').'</b>';
                    break;
                case Composite::LOGICAL_AND:
                    $name .= '<b>'.__('TEC', 'tree', 'operatorAnd').'</b>';
                    break;
            }
        } else {
            /** @var Leaf $node */
            $name .= $node->getName();
        }

        return $name;
    }

}