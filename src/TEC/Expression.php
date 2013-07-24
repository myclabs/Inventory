<?php
/**
 * @author  valentin.claras
 * @package TEC
 * @package Expression
 */

namespace TEC;

use TEC\Algo\Logic;
use TEC\Algo\Numeric;
use TEC\Algo\Select;
use TEC\Component\Composite;
use TEC\Algo\Algo;
use TEC\Exception\InvalidExpressionException;
use Core_Exception_InvalidArgument;
use Core_Exception_UndefinedAttribute;

/**
 * @package    TEC
 * @subpackage Expression
 */
class Expression
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
     * Cache vers l'algo.
     *
     * @var Algo
     */
    protected $algo = null;


    /**
     * Constructeur.
     *
     * @param string $expression
     * @param string $type
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function __construct($expression, $type=null)
    {
        $this->expression = $expression;

        if ($type !== null) {
            if (!in_array($type, array(self::TYPE_NUMERIC, self::TYPE_LOGICAL, self::TYPE_SELECT))) {
                throw new Core_Exception_InvalidArgument('Invalid Type.');
            }
            $this->type = $type;
        } else {
            $this->detectType();
        }
    }

    /**
     * Renvoi l'expression.
     *
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * Détecte le type d'expression.
     *
     * @throws InvalidExpressionException
     */
    public function detectType()
    {
        // Mise à jour de l'attribut $type en fonction de l'expression.
        $matchNumeric = preg_match('#[\+\-\*\/]#', $this->expression);
        $matchLogical = preg_match('#[\&\|\!]#', $this->expression);
        $matchSelect = preg_match('#[\:]#', $this->expression);
        if ($matchNumeric && !$matchLogical && !$matchSelect) {
            $this->type = self::TYPE_NUMERIC;
        } else if (!$matchNumeric && $matchLogical && !$matchSelect) {
            $this->type = self::TYPE_LOGICAL;
        } else if (!$matchNumeric && !$matchLogical && $matchSelect) {
            $this->type = self::TYPE_SELECT;
        } else {
            throw new InvalidExpressionException('Invalid Expression.', $this->expression);
        }
    }

    /**
     * Indique le type de l'expression.
     *
     * @see self::TYPE_NUMERIC
     * @see self::TYPE_LOGICAL
     * @see self::TYPE_SELECT
     *
     * @throws Core_Exception_UndefinedAttribute
     *
     * @return string
     */
    public function getType()
    {
        if ($this->type === null) {
            throw new Core_Exception_UndefinedAttribute('The Type has not been defined yet.');
        }
        return $this->type;
    }

    /**
     * Renvoi l'algo correspondant au type d'expression.
     *
     * @return Algo
     */
    protected function getAlgo()
    {
        if ($this->algo === null) {
            switch ($this->getType()) {
                case self::TYPE_NUMERIC:
                    $this->algo = new Numeric($this->getExpression());
                    break;
                case self::TYPE_LOGICAL:
                    $this->algo = new Logic($this->getExpression());
                    break;
                case self::TYPE_SELECT:
                    $this->algo = new Select($this->getExpression());
                    break;
            }
        }

        return $this->algo;
    }

    /**
     * Vérifie que l'expression est valide.
     *
     * @throws InvalidExpressionException
     */
    public function check()
    {
        $errors = $this->getErrors();
        if (count($errors) !== 0) {
            throw new InvalidExpressionException("The Expression is invalid, can't build the tree", $this->expression, $errors);
        }
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
     * Méthode qui permet de traiter l'arbre pour ensuite l'afficher sous la forme d'un graph.
     *
     * @return array $expression
     */
    public function getRootNode()
    {
        return $this->getAlgo()->getRootNode();
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
    public function getAsString()
    {
        return $this->getAlgo()->convertTreeToString();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getAsString();
    }

}
