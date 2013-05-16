<?php
/**
 * @author  valentin.claras
 * @author  yoann.croizer
 * @author  hugo.charbonnier
 * @package TEC
 */

/**
 * @package    TEC
 * @subpackage Model
 */
class TEC_Model_Expression extends Core_Model_Entity
{

    /**
     * Constante utilisée pour définir une expression numérique.
     *
     * @var int
     */
    const TYPE_NUMERIC = 'numeric';

    /**
     * Constante utilisée pour définir une expression logique.
     *
     * @var int
     */
    const TYPE_LOGICAL = 'logical';

    /**
     * Constante utilisée pour définir une expression de selection.
     *
     * @var int
     */
    const TYPE_SELECT = 'select';


    /**
     * Identifiant unique d'une expression.
     *
     * @var int
     */
    protected $id;

    /**
     * Contient l'expression.
     *
     * @var String
     */
    protected $expression = null;

    /**
     * Type de l'algo à éxécuter
     *  - self::TYPE_NUMERIC;
     *  - self::TYPE_LOGIC;
     *  - self::TYPE_SELECT;
     *
     * @var int
     */
    protected $type = null;

    /**
     * Noeud racine de l'arbre associé à l'expression.
     *
     * @var TEC_Model_Composite
     */
    protected $rootNode = null;

    /**
     * Cache vers l'algo.
     *
     * @var TEC_Expression_Algo
     */
    protected $_algo = null;


    /**
     * Constructeur.
     * @param string $expression
     */
    public function __construct($expression=null, $type=null)
    {
        if ($type !== null) {
            $this->setType($type);
        }
        if ($expression !== null) {
            $this->setExpression($expression);
        }
    }

    /**
     * Fonction appelé avant un update de l'objet (défini dans le mapper).
     */
    public function preSave()
    {
        if ($this->rootNode === null) {
            $this->buildTree();
        }
    }

    /**
     * Spécifie le type de l'expression.
     *
     * @param const type
     *
     * @see self::TYPE_NUMERIC
     * @see self::TYPE_LOGICAL
     * @see self::TYPE_SELECT
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function setType($type)
    {
        if (!in_array($type, array(self::TYPE_NUMERIC, self::TYPE_LOGICAL, self::TYPE_SELECT))) {
            throw new Core_Exception_InvalidArgument('Invalid Type.');
        }
        if ($this->type !== $type) {
            $this->type = $type;

            $this->_algo = null;
            if ($this->rootNode !== null) {
                $this->buildTree();
            }
        }
    }

    /**
     * Indique le type de l'expression.
     *
     * @return const
     *
     * @see self::TYPE_NUMERIC
     * @see self::TYPE_LOGICAL
     * @see self::TYPE_SELECT
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function getType()
    {
        if ($this->type === null) {
            throw new Core_Exception_UndefinedAttribute('The Type has not been defined yet.');
        }
        return $this->type;
    }

    /**
     * Spécifie l'expression.
     *
     * @param string $expression
     *
     * @throws TEC_Model_InvalidExpressionException
     */
    public function setExpression($expression)
    {
        // Mise à jour de l'attribut $type si nécessaire.
        if ($this->type === null) {
            $matchNumeric = preg_match('#[\+\-\*\/]#', $expression);
            $matchLogical = preg_match('#[\&\|\!]#', $expression);
            $matchSelect = preg_match('#[\:]#', $expression);
            if ($matchNumeric && !$matchLogical && !$matchSelect) {
                $this->type = self::TYPE_NUMERIC;
            } else if (!$matchNumeric && $matchLogical && !$matchSelect) {
                $this->type = self::TYPE_LOGICAL;
            } else if (!$matchNumeric && !$matchLogical && $matchSelect) {
                $this->type = self::TYPE_SELECT;
            } else {
                throw new TEC_Model_InvalidExpressionException('Invalid Expression.', $expression);
            }
        }

        $this->expression = $expression;
        $this->_algo = null;
        if ($this->rootNode !== null) {
            $this->buildTree();
        }
    }

    /**
     * Renvoi l'expression.
     *
     * @throws Core_Exception_UndefinedAttribute
     * @return string
     */
    public function getExpression()
    {
        if ($this->expression === null) {
            throw new Core_Exception_UndefinedAttribute('The Expression has not been defined yet.');
        }
        return $this->expression;
    }

    /**
     * Récupère le noeud racine de l'arbre associé à l'expression.
     *
     * @return TEC_Model_Composite $root
     */
    public function getRootNode()
    {
        if ($this->rootNode === null) {
            if ($this->expression === null) {
                throw new Core_Exception_UndefinedAttribute('The Expression has not been defined yet.');
            }
            throw new Core_Exception_UndefinedAttribute('No Tree as been created yet, try use buildTree().');
        }
        return $this->rootNode;
    }

    /**
     * Renvoi l'algo correspondant au type d'expression.
     *
     * @return TEC_Expression_Algo
     */
    protected function getAlgo()
    {
        if ($this->_algo === null) {
            $expression = $this->getExpression();
            switch ($this->type) {
                case self::TYPE_NUMERIC:
                    $this->_algo = new TEC_Expression_Algo_Numeric($expression, $this->rootNode);
                    break;
                case self::TYPE_LOGICAL:
                    $this->_algo = new TEC_Expression_Algo_Logic($expression, $this->rootNode);
                    break;
                case self::TYPE_SELECT:
                    $this->_algo = new TEC_Expression_Algo_Select($expression, $this->rootNode);
                    break;
            }
        }

        return $this->_algo;
    }

    /**
     * Renvois un tableau des erreurs que comporte l'expression.
     *
     * @return array $errors
     */
    public function getErrors()
    {
        return $this->getAlgo()->getErrors();
    }

    /**
     * Vérifie que l'expression est valide.
     *
     * @throws TEC_Model_InvalidExpressionException
     */
    public function check()
    {
        $errors = $this->getErrors();
        if (count($errors) !== 0) {
            throw new TEC_Model_InvalidExpressionException("The Expression is invalid, can't build the tree",
                                                           $this->expression, $errors);
        }
    }

    /**
     * Construit l'arbre correspondant à l'expression.
     *
     * @return TEC_Model_Composite
     */
    public function buildTree()
    {
        $this->check();
        $this->rootNode = $this->getAlgo()->createTree();
    }

    /**
     * Méthode qui permet de traiter l'arbre pour ensuite l'afficher sous la forme d'un graph.
     *
     * @return array $expression
     */
    public function getGraph()
    {
        return $this->getAlgo()->convertTreeToGraph();
    }

    /**
     * Méthode permettant d'afficher l'arbre sous forme de string.
     *
     * @return string
     */
    public function getTreeAsString()
    {
        return $this->getAlgo()->convertTreeToString();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getTreeAsString();
    }

}
