<?php
/**
 * @author valentin.claras
 * @author yoann.croizer
 * @author hugo.charbonnier
 * @package Exec
 */

/**
 * classe Exec_Execution
 * @package Exec
 */
abstract class Exec_Execution
{
    /**
     * L'expression à partir de laquelle l'arbre est construit.
     *
     * @var TEC_Model_Expression
     */
    protected $expression;


    /**
     * Constructeur de la classe.
     *
     * @param TEC_Model_Expression $expression
     */
    public function __construct(TEC_Model_Expression $expression)
    {
        $this->expression = $expression;
    }

    /**
     * Renvoi l'attribut protégé expression
     * @return TEC_Model_Expression
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
     * @param TEC_Model_Component          $node
     * @param Exec_Interface_ValueProvider $valueProvider
     *
     * @return array
     */
    protected abstract function getErrorsFromComponent(TEC_Model_Component $node,
                                                       Exec_Interface_ValueProvider $valueProvider);

    /**
     * Méthode récursive qui va parcourir l'arbre et renvoyer le résultat de son éxécution.
     *
     * @param TEC_Model_Component          $node
     * @param Exec_Interface_ValueProvider $valueProvider
     *
     * @return mixed
     */
    protected abstract function executeComponent(TEC_Model_Component $node,
                                                 Exec_Interface_ValueProvider $valueProvider);

}
