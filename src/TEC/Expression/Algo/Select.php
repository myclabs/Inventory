<?php
/**
 * @author valentin.claras
 * @author yoann.croizer
 * @author hugo.charbonnier
 * @package    TEC
 * @subpackage Expression
 */

/**
 * Description des expressions de type execution et méthodes permettant leur manipulation.
 * @package    TEC
 * @subpackage Expression
 */
class TEC_Expression_Algo_Select extends TEC_Expression_Algo
{
    protected $mandatoryCharacters = '\:';


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
                case ';':
                    if ($nbBrackets > 0) {
                        $tempExpression .= $character;
                    } else {
                        if (!$hasFirstLevelSymbol) {
                            $hasFirstLevelSymbol = true;
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
        if (!($hasFirstLevelSymbol) || (trim($tempExpression) !== '')) {
            $expressionParts[] = $tempExpression;
        }

        if ($hasFirstLevelSymbol) {
            foreach ($expressionParts as $subExpression) {
                $errors = array_merge($errors, $this->getSpecificErrors($subExpression));
            }
        } else {
            if (preg_match("#^\(.*\)$#", $expression)) {
                $errors = array_merge($errors, $this->getSpecificErrors(substr($expression, 1, -1)));
            } else if (preg_match("#^\(#", $expression)) {
                $errors[] = __('TEC', 'syntaxError', 'openingBracketNotClosed', array('PART' => $expression));
                $errors = array_merge($errors, $this->getSpecificErrors(substr($expression, 1)));
            } else if (strpos($expression, ':') !== false) {
                list($condition, $selection) = explode(':', $expression, 2);
                $condition = trim($condition);
                $selection = trim($selection);
                if (($condition === '') && (preg_match("#[^\w]#", $condition))) {
                    $errors[] = __('TEC', 'syntaxError', 'invalidCondition', array('PART' => $condition));
                }
                if ($selection === '') {
                    $errors[] = __('TEC', 'syntaxError', 'emptySelection', array('PART' => $expression));;
                } else if (preg_match("#^\(.*\)$#s", $selection)) {
                    $errors = array_merge($errors, $this->getSpecificErrors(substr($selection, 1, -1)));
                } else if (!preg_match("#^\w+$#", $selection)) {
                    $errors[] = __('TEC', 'syntaxError', 'invalidSelection', array('PART' => $selection));
                }
            } else {
                $errors[] = __('TEC', 'syntaxError', 'missingOperator', array('PART' => $expression));
            }
        }

        return $errors;
    }

    /**
     * Méthode qui controle et modifie si besoin est, le parenthésage de l'expression.
     *
     * @return string
     */
    public function correctBrackets()
    {
        return $this->expression;
    }

    /**
     * Créer un arbre à partir d'une expression.
     *  Fonction commune aux algos Numeric et Logic. Surchargée pour Select.
     *
     * @param string              $expression
     * @param TEC_Model_Composite $parentNode
     */
    protected function buildTree($expression, $parentNode)
    {
        $parentNode->setOperator(TEC_Model_Composite::SELECT);

        $nodeName = '';
        $expressionTab = str_split($expression);
        $sizeExpression = count($expressionTab);

        for ($position = 0; $position < $sizeExpression; $position++) {
            $character = $expressionTab[$position];
            switch ($character) {
                case ':' :
                    $end = $this->getEndSubExpression($expression, $position + 1);
                    $insideExpression = substr($expression, $position + 1, ($end - $position + 1));
                    if ($nodeName === '') {
                        $this->buildTree($insideExpression, $parentNode);
                    } else {
                        $childNode = new TEC_Model_Composite();
                        $this->buildTree($insideExpression, $childNode);
                        $childNode->setModifier($nodeName);
                        $parentNode->addChild($childNode);
                        $nodeName = '';
                    }
                    $position = $end;
                    break;
                default:
                    if (preg_match('#[\s\v\(\);]#', $character)) {
                        break;
                    }
                    $nodeName .= $character;
                    break;
            }
        }
        if (trim($nodeName) !== '') {
            $leaf = new TEC_Model_Leaf();
            $leaf->setName($nodeName);
            $parentNode->addChild($leaf);
        }
    }

    /**
     * Indique la fun d'une sous expression.
     *
     * @param string $expression
     * @param int    $start
     *
     * @return int
     */
    protected function getEndSubExpression($expression, $start)
    {
        $subExpressionTab = str_split($expression);
        $sizeSubExpression = count($subExpressionTab);
        $nbBrackets = 0;

        for ($position = $start; $position < $sizeSubExpression; $position++) {
            $character = $subExpressionTab[$position];
            if ($character === '(') {
                $nbBrackets ++;
            }
            if (($character === ')') && ($nbBrackets > 0)) {
                $nbBrackets --;
            }
            if (($character === ';') && ($nbBrackets === 0)) {
                return $position - 1;
            }
        }

        return $position;
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

        if ($node instanceof TEC_Model_Leaf) {
            $expression .= ': ' . $node->getName();
        } else {
            if ($node->getParent() !== null) {
                $expression .= $node->getModifier() . ' ';
            }
            $subExpression = '';
            foreach ($node->getChildren() as $child) {
                $subExpression .= $this->convertNodeToString($child) . ' ; ';
            }
            $subExpression = substr($subExpression, null, -3);
            if (preg_match('#(^[^:])|;#', $subExpression) && (trim($expression) !== '')) {
                $subExpression = ': (' . $subExpression . ')';
            }
            $expression .= $subExpression;
        }

        return $expression;
    }
    /**
     * Méthode qui donne une représentation d'un Tree sous forme de graph.
     *
     * @return array
     */
    public function convertTreeToGraph()
    {
        $expression = '';

        $rootNode = $this->getRootNode();

        $expression .= $this->buildGraph($rootNode, null);

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
        if ($node instanceof TEC_Model_Composite) {
            $name = $node->getModifier() . __('TEC', 'tree', 'questionMark');
        } else {
            $name = $node->getName();
        }

        return $name;
    }

}