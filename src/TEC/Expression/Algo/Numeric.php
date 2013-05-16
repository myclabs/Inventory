<?php
/**
 * @author valentin.claras
 * @author yoann.croizer
 * @author hugo.charbonnier
 * @package    TEC
 * @subpackage Expression
 */

/**
 * Description des expressions numériques et méthodes permettant leur manipulation.
 * @package    TEC
 * @subpackage Expression
 */
class TEC_Expression_Algo_Numeric extends TEC_Expression_Algo
{
    protected $mandatoryCharacters = '\+\-\*\/';


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
            $addSymbol = $this->getSymbol($node, TEC_Model_Component::MODIFIER_ADD);
            $subSymbol = $this->getSymbol($node, TEC_Model_Component::MODIFIER_SUB);
            $addChildren = array();
            $subChildren = array();
            foreach ($node->getChildren() as $child) {
                if ($child->getModifier() === TEC_Model_Component::MODIFIER_SUB) {
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
     * @param TEC_Model_Composite $node
     * @param string              $modifier
     *
     * @return string
     */
    protected function getSymbol($node, $modifier)
    {
        switch ($node->getOperator()) {
            case TEC_Model_Composite::OPERATOR_SUM:
                if ($modifier === TEC_Model_Component::MODIFIER_SUB) {
                    return ' - ';
                } else {
                    return ' + ';
                }
                break;
            case TEC_Model_Composite::OPERATOR_PRODUCT:
                if ($modifier === TEC_Model_Component::MODIFIER_SUB) {
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
     * @param TEC_Model_Component $arrayNode
     * @param string $operator
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
     * Méthode indiquant le nom d'un noeud dans un graph.
     *
     * @param TEC_model_Composite $node
     *
     * @return string
     */
    protected function getNodeGraphName($node)
    {
        $name = '';

        $parentNode = $node->getParent();
        if (($parentNode !== null) && ($parentNode->getOperator() === TEC_Model_Composite::OPERATOR_PRODUCT)) {
            switch ($node->getModifier()) {
                case TEC_Model_Component::MODIFIER_SUB:
                    $name .= __('TEC', 'tree', 'modifierProductSub');
                    break;
            }
        } else {
            switch ($node->getModifier()) {
                case TEC_Model_Component::MODIFIER_ADD:
                    $name .= __('TEC', 'tree', 'modifierSumAdd');
                    break;
                case TEC_Model_Component::MODIFIER_SUB:
                    $name .= __('TEC', 'tree', 'modifierSumSub');
                    break;
            }
        }
        if ($node instanceof TEC_Model_Composite) {
            switch ($node->getOperator()) {
                case TEC_Model_Composite::OPERATOR_SUM:
                    $name .= '<b>'.__('TEC', 'tree', 'operatorSum').'</b>';
                    break;
                case TEC_Model_Composite::OPERATOR_PRODUCT:
                    $name .= '<b>'.__('TEC', 'tree', 'operatorProduct').'</b>';
                    break;
            }
        } else {
            $name .= $node->getName();
        }

        return $name;
    }

}