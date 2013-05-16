<?php
/**
 * @author valentin.claras
 * @package    TEC
 * @subpackage Expression
 */

/**
 * Classe contenant les méthode de traitement des expressions.
 *
 * @package    TEC
 * @subpackage Expression
 */
abstract class TEC_Expression_Algo
{
    /**
     * Liste des caractères spéciaux obligatoires dans une expression.
     *
     * @var string
     */
    protected $mandatoryCharacters = '';

    /**
     * Expression.
     *
     * @var string
     */
    protected $expression = null;

    /**
     * Noeud racine de l'arbre.
     *
     * @var TEC_Model_Composite
     */
    protected $rootNode = null;


    /**
     * Constructeur.
     *
     * @param String $expression
     * @param TEC_Model_Composite $rootNode
     */
    public function __construct($expression, $rootNode=null)
    {
        $this->expression = preg_replace(array("#\((\s*\w*\s*)\)#"), array('$1'), $expression);
        $this->rootNode = $rootNode;
    }

    /**
     * Renvoi le noeud racine de l'arbre.
     *
     * @throws Core_Exception_UndefinedAttribute
     *
     * @return TEC_Model_Composite
     */
    protected function getRootNode()
    {
        if ($this->rootNode === null) {
            $this->createTree();
        }
        return $this->rootNode;
    }
    /**
     * Renvoi un éventuel tableau des erreurs de l'expression.
     *
     * @return array
     */
    public function getErrors()
    {
        if (!preg_match('#['.$this->mandatoryCharacters.']#', $this->expression)) {
            return array(__('TEC', 'syntaxError', 'missingOperator', array('PART' => $this->expression)));
        }

        return $this->getSpecificErrors($this->expression);
    }

    /**
     * Renvoi les erreurs d'une expression donnée.
     *
     * @param string $expression
     *
     * @return array
     */
    abstract protected function getSpecificErrors($expression);

    /**
     * Construit l'arbre correspondant à l'expression.
     *
     * @return TEC_Model_Composite
     */
    public function createTree()
    {
        $this->rootNode = new TEC_Model_Composite();
        $this->buildTree($this->correctBrackets(), $this->rootNode);
        return $this->rootNode;
    }

    /**
     * Méthode qui controle et modifie si besoin est, le parenthésage de l'expression.
     *
     * @return string
     */
    public function correctBrackets()
    {
        return $this->reformBrackets($this->expression)['expression'];
    }

    /**
     * Méthode qui modifie le parenthésage d'une expression donnée.
     *
     * Notamment, cette méthode isole les multiplications et les divisisons du reste
     *  des opérations en les mettant entre parenthèses.
     *
     * @param string $expression
     *
     * @return string
     */
    protected function reformBrackets($expression)
    {
        $formattedExpression = '';
        $expressionTab = str_split($expression);
        $sizeExpression = count($expressionTab);
        $topLevelSymbol = null;
        $lastSymbol = null;
        $tempExpression = '';

        // On parcourt chaque caracatère de l'expression.
        for ($position = 0; $position < $sizeExpression; $position++) {
            $character = $expressionTab[$position];
            switch ($character) {
                case '(' :
                    $nbBrackets = 1;
                    $position++;
                    $insideExpression = '(';
                    // Récupération de l'expression entre parenthèses.
                    while (($position < $sizeExpression) && ($nbBrackets != 0)) {
                        $insideExpression .= $expressionTab[$position];
                        if ($expressionTab[$position] === '(') {
                            $nbBrackets++;
                        } else if ($expressionTab[$position] === ')') {
                            $nbBrackets--;
                        }
                        $position++;
                    }
                    // Suppression des parenthèses autour de l'expression.
                    $insideExpression = substr($insideExpression, 1, -1);
                    $position--;
                    // Traitement de la sous expression.
                    $reformBrackets = $this->reformBrackets($insideExpression);
                    $insideExpression = $reformBrackets['expression'];
                    if ((in_array($lastSymbol, array('*', '/', '&'))
                            XOR in_array($reformBrackets['topLevelSymbol'], array('*', '/', '&')))
                    ) {
                        $insideExpression = '(' . $insideExpression . ')';
                    } else {
                        $lastBracketSymbol = $reformBrackets['topLevelSymbol'];
                        $lastSymbol = 'brackets';
                    }
                    $tempExpression .= $insideExpression;
                    break;
                case '+':
                case '-':
                case '|':
                    // Stocxage du topLevelSybol.
                    $topLevelSymbol = $character;
                    // Traitement du dernier symbole.
                    if (in_array($lastSymbol, array('*', '/', '&'))) {
                        $tempExpression = '('.$tempExpression.')';
                    } else if (($lastSymbol === 'brackets') && !(in_array($lastBracketSymbol, array('+', '-', '|')))) {
                        $tempExpression = '(' . $tempExpression . ')';
                    }
                    // Passage des caractères stockés dans la chaîne finale.
                    $formattedExpression .= $tempExpression . $character;
                    $tempExpression = '';
                    $lastSymbol = $character;
                    break;
                case '*':
                case '/':
                case '&' :
                    // Stocxage du topLevelSybol.
                    if ($topLevelSymbol === null) {
                        $topLevelSymbol = $character;
                    }
                    // Traitement du dernier symbole.
                    if (($lastSymbol === 'brackets') && !(in_array($lastBracketSymbol, array('*', '/', '&')))) {
                        $tempExpression = '(' . $tempExpression . ')';
                    }
                    // Stockage du caractère.
                    $tempExpression .= $character;
                    $lastSymbol = $character;
                    break;
                default:
                    $tempExpression .= $character;
                    break;
            }
        }
        // On traite la dernière chaine $tempExpression.
        if (($formattedExpression !== '') && (trim($tempExpression) !== '')
            && (in_array($lastSymbol, array('*', '/', '&')))) {
            $tempExpression = '('.$tempExpression.')';
        }
        $formattedExpression .= $tempExpression;

        return array('expression' => $formattedExpression, 'topLevelSymbol' => $topLevelSymbol);
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
        $expressionTab = str_split($expression);
        $sizeExpression = count($expressionTab);
        $lastSymbol = null;

        for ($position = 0; $position < $sizeExpression; $position++) {
            $character = $expressionTab[$position];
            switch ($character) {
                case '+' :
                case '-' :
                    $parentNode->setOperator(TEC_Model_Composite::OPERATOR_SUM);
                    $lastSymbol = $character;
                    break;
                case '*' :
                case '/' :
                    $parentNode->setOperator(TEC_Model_Composite::OPERATOR_PRODUCT);
                    $lastSymbol = $character;
                    break;
                case '&' :
                    $parentNode->setOperator(TEC_Model_Composite::LOGICAL_AND);
                    break;
                case '|' :
                    $parentNode->setOperator(TEC_Model_Composite::LOGICAL_OR);
                    break;
                case '!' :
                    $lastSymbol = $character;
                    break;
                case '(' :
                    $nbBrackets = 1;
                    $insideExpression = '(';
                    $position++;
                    // On commence par récupérer l'expression comprise dans les parenthèses.
                    while (($nbBrackets != 0) && ($position < $sizeExpression)) {
                        $insideExpression .= $expressionTab[$position];
                        if ($expressionTab[$position] == '(') {
                            $nbBrackets++;
                        } else if ($expressionTab[$position] == ')') {
                            $nbBrackets--;
                        }
                        $position++;
                    }
                    // Suppression des parenthèses autour de l'expression.
                    $insideExpression = substr($insideExpression, 1, -1);
                    $position--;
                    // Création du composite enfant et traitement de l'expression associée.
                    $childNode = new TEC_Model_Composite();
                    $this->applyModifierToComponent($lastSymbol, $childNode);
                    $lastSymbol = null;
                    $this->buildTree($insideExpression, $childNode);
                    $parentNode->addChild($childNode);
                    break;
                default:
                    if (preg_match('#[\s\v]#', $character)) {
                        break;
                    }
                    // Création de la feuille.
                    $leaf = new TEC_Model_Leaf();
                    $this->applyModifierToComponent($lastSymbol, $leaf);
                    $lastSymbol = null;
                    // Récupéreration de l'ensemble du nom de l'élément de calcul.
                    $leafName = '';
                    while (($position < $sizeExpression) && (preg_match('#[\w]#', $expressionTab[$position]))) {
                        $leafName .= $expressionTab[$position];
                        $position++;
                    }
                    $position--;
                    $leaf->setName($leafName);
                    $parentNode->addChild($leaf);
                    break;
            }
        }
    }

    /**
     * Ajoute le bon modifier au component donné.
     *
     * @param string              $modifier
     * @param TEC_Model_Component $component
     */
    protected function applyModifierToComponent($modifier, $component)
    {
        switch ($modifier) {
            case '!' :
                $component->setModifier(TEC_Model_Component::MODIFIER_NOT);
                break;
            case '-' :
            case '/' :
                $component->setModifier(TEC_Model_Component::MODIFIER_SUB);
                break;
            case '+' :
            case '*' :
                $component->setModifier(TEC_Model_Component::MODIFIER_ADD);
            default:
                if ($this instanceof TEC_Expression_Algo_Numeric) {
                    $component->setModifier(TEC_Model_Component::MODIFIER_ADD);
                }
                break;
        }
    }

    /**
     * Méthode qui donne une représentation d'un Tree sous forme de string.
     *
     * @return string
     */
    public function convertTreeToString()
    {
        return $this->convertNodeToString($this->getRootNode());
    }

    /**
     * Transcrit un Node sous forme de string.
     *
     * @param TEC_Model_Component $node
     *
     * @return string
     */
    abstract protected function convertNodeToString($node);

    /**
     * Méthode qui donne une représentation d'un Tree sous forme de graph.
     *
     * @return array
     */
    public function convertTreeToGraph()
    {
        $expression = '';

        $rootNode = $this->getRootNode();

        $expression .= '[';
        $expression .= '{v:"0",f:"'.$this->getNodeGraphName($rootNode).'"},';
        $expression .= '"",';
        $expression .= '"'.$this->getNodeGraphDescription($rootNode).'"';
        $expression .= '],';
        $expression .= $this->buildGraph($rootNode);

        return $expression;
    }

    /**
     * Méthode qui permet l'affichage de l'arbre de calcul.
     *
     * @param TEC_Model_Composite $node
     * @param int                 $parentIdentifier
     *
     * @return string
     */
    protected function buildGraph($node, $parentIdentifier=0)
    {
        $expression = '';

        foreach ($node->getChildren() as $position => $child) {
            $expression .= '[';
            $expression .= '{v:"'.$parentIdentifier.'-'.$position.'",f:"'.$this->getNodeGraphName($child).'"},';
            $expression .= '"'.$parentIdentifier.'",';
            $expression .= '"'.$this->getNodeGraphDescription($child).'"';
            $expression .= '],';
            if ($child instanceof TEC_Model_Composite) {
                $expression .= $this->buildGraph($child, $parentIdentifier.'-'.$position);
            }
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
    protected abstract function getNodeGraphName($node);

    /**
     * Méthode indiquant la description d'un noeud dans un graph.
     *
     * @param TEC_model_Composite $node
     *
     * @return string
     */
    protected function getNodeGraphDescription($node)
    {
        return '';
    }

}