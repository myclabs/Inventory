<?php
/**
 * @author     valentin.claras
 * @package    Exec
 * @subpackage Execution
 */

namespace Exec;

use Exec\Provider\ValueInterface;
use TEC\Expression;
use TEC\Component\Component;

/**
 * classe Execution
 * @package    Exec
 * @subpackage Execution
 */
abstract class Execution
{
    /**
     * L'expression à partir de laquelle l'arbre est construit.
     *
     * @var Expression
     */
    protected $expression;


    /**
     * Constructeur de la classe.
     *
     * @param Expression $expression
     */
    public function __construct(Expression $expression)
    {
        $this->expression = $expression;
    }

    /**
     * Renvoi l'attribut protégé expression
     * @return Expression
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * Parcourt l'arbre d'éxécution des opérations de l'arbres et vérifie la valeur.
     *
     * @param \Exec\Provider\ValueInterface $valueProvider
     *
     * @return array
     */
    public function getErrors(ValueInterface $valueProvider)
    {
        return $this->getErrorsFromComponent($this->expression->getRootNode(), $valueProvider);
    }

    /**
     * Parcourt l'arbre d'éxécution des opérations de l'arbres et parse les valeurs.
     *
     * @param \Exec\Provider\ValueInterface $valueProvider
     *
     * @return mixed
     */
    public function executeExpression(ValueInterface $valueProvider)
    {
        return $this->executeComponent($this->expression->getRootNode(), $valueProvider);
    }

    /**
     * Méthode récursive qui va parcourir l'arbre et vérifier les composants pour son éxécution.
     *
     * @param Component $node
     * @param \Exec\Provider\ValueInterface $valueProvider
     *
     * @return array
     */
    protected abstract function getErrorsFromComponent(
        Component $node,
        ValueInterface $valueProvider
    );

    /**
     * Méthode récursive qui va parcourir l'arbre et renvoyer le résultat de son éxécution.
     *
     * @param Component $node
     * @param \Exec\Provider\ValueInterface $valueProvider
     *
     * @return mixed
     */
    protected abstract function executeComponent(
        Component $node,
        ValueInterface $valueProvider
    );

}
