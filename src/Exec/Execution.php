<?php
/**
 * @author valentin.claras
 * @author yoann.croizer
 * @author hugo.charbonnier
 * @package Exec
 */

use TEC\Expression;
use TEC\Component\Component;

/**
 * classe Exec_Execution
 * @package Exec
 */
abstract class Exec_Execution
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
     * @param Exec_Interface_ValueProvider $valueProvider
     *
     * @return array
     */
    public function getErrors(Exec_Interface_ValueProvider $valueProvider)
    {
        return $this->getErrorsFromComponent($this->expression->getRootNode(), $valueProvider);
    }

    /**
     * Parcourt l'arbre d'éxécution des opérations de l'arbres et parse les valeurs.
     *
     * @param Exec_Interface_ValueProvider $valueProvider
     *
     * @return mixed
     */
    public function executeExpression(Exec_Interface_ValueProvider $valueProvider)
    {
        return $this->executeComponent($this->expression->getRootNode(), $valueProvider);
    }

    /**
     * Méthode récursive qui va parcourir l'arbre et vérifier les composants pour son éxécution.
     *
     * @param Component          $node
     * @param Exec_Interface_ValueProvider $valueProvider
     *
     * @return array
     */
    protected abstract function getErrorsFromComponent(Component $node,
                                                       Exec_Interface_ValueProvider $valueProvider);

    /**
     * Méthode récursive qui va parcourir l'arbre et renvoyer le résultat de son éxécution.
     *
     * @param Component          $node
     * @param Exec_Interface_ValueProvider $valueProvider
     *
     * @return mixed
     */
    protected abstract function executeComponent(Component $node,
                                                 Exec_Interface_ValueProvider $valueProvider);

}
