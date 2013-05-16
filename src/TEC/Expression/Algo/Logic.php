<?php
/**
 * @author yoann.croizer
 * @author hugo.charbonnier
 * @package TEC
 */

/**
 * Description des expressions logiques et méthodes permettant leur manipulation.
 * @package TEC
 * @subpackage Expression
 */
class TEC_Expression_Algo_Logic extends TEC_Expression_Algo
{
    protected $mandatoryCharacters = '\&\|';


    /**
     * Renvoi les erreurs d'une expression donnée.
     *
     * @param string $expression
     *
     * @return array
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
     * Transcrit un Node sous forme de string.
     *
     * @param TEC_Model_Component $node
     *
     * @return string
     */
    protected function convertNodeToString($node)
    {
        $expression = '';

        if ($node instanceof  TEC_Model_Leaf) {
            $expression .= $node->getName();
        } else {
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
        if ($node->getModifier() == TEC_Model_Component::MODIFIER_NOT) {
            $expression = '!'.$expression;
        }

        return $expression;
    }

    /**
     * Permet de récupérer le symbol d'un noeud.
     *
     * @param TEC_Model_Composite $node
     *
     * @return string
     */
    protected function getSymbol($node)
    {
        switch ($node->getOperator()) {
            case TEC_Model_Composite::LOGICAL_AND:
                return ' & ';
                break;
            case TEC_Model_Composite::LOGICAL_OR:
                return ' | ';
                break;
            default:
                throw new Core_Exception_UndefinedAttribute('None symbol has been defined yet.');
        }
    }

    /**
     * Méthode indiquant le nom d'un noeud dans un graph.
     *
     * @param TEC_model_Composite $node
     *
     * @return string
     */
    protected function getNodeGraphName($node)
    {
        $name = '';

        switch ($node->getModifier()) {
            case TEC_Model_Component::MODIFIER_NOT:
                $name .= __('TEC', 'tree', 'modifierLogicalNot');
                break;
        }
        if ($node instanceof TEC_Model_Composite) {
            switch ($node->getOperator()) {
                case TEC_Model_Composite::LOGICAL_OR:
                    $name .= '<b>'.__('TEC', 'tree', 'operatorOr').'</b>';
                    break;
                case TEC_Model_Composite::LOGICAL_AND:
                    $name .= '<b>'.__('TEC', 'tree', 'operatorAnd').'</b>';
                    break;
            }
        } else {
            $name .= $node->getName();
        }

        return $name;
    }

}