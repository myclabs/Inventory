<?php
/**
 * @author valentin.claras
 * @author yoann.croizer
 * @author hugo.charbonnier
 * @author matthieu.napoli
 * @package    Exec
 * @subpackage Execution
 */

use TEC\Component\Component;
use TEC\Component\Composite;
use TEC\Component\Leaf;

/**
 * classe Exec_Execution_Select
 * @package    Exec
 * @subpackage Execution
 */
class Exec_Execution_Select extends Exec_Execution
{
    /**
     * Parcourt l'arbre d'éxécution des opérations de l'arbres et vérifie la valeur.
     *
     * Pourquoi surcharger getErrors ?
     *  Car le premier node d'une expression Select est toujours vide.
     *
     * @param Exec_Interface_ValueProvider $valueProvider
     *
     * @return Array
     */
    public function getErrors(Exec_Interface_ValueProvider $valueProvider)
    {
        return $this->getErrorsFromComponent($this->expression->getRootNode(), $valueProvider);
    }

    /**
     * Méthode récursive qui va parcourir l'arbre et vérifier les composants pour son éxécution.
     *
     * @param Component          $node
     * @param Exec_Interface_ValueProvider $valueProvider
     *
     * @return array
     */
    protected function getErrorsFromComponent(Component $node, Exec_Interface_ValueProvider $valueProvider)
    {
        $errors = array();

        if ($node instanceof Leaf) {
            $errors = array_merge($errors, $valueProvider->checkValueForExecution($node->getName()));
        } elseif ($node instanceof Composite) {
            foreach ($node->getChildren() as $child) {
                $errors = array_merge($errors, $this->getErrorsFromComponent($child, $valueProvider));
            }
        }

        return $errors;
    }

    /**
     * Selectionne l'algo d'éxécution des opérations de l'arbres en fonction du type d'expression.
     *
     * @param Exec_Interface_ValueProvider $valueProvider
     *
     * @return array
     */
    public function executeExpression(Exec_Interface_ValueProvider $valueProvider)
    {
        $results = array();

        foreach ($this->expression->getRootNode()->getChildren() as $rootNode) {
            $results = array_merge($results, $this->executeComponent($rootNode, $valueProvider));
        }

        return $results;
    }

    /**
     * Méthode récursive qui va parcourir l'arbre et renvoyer le résultat de son éxécution.
     *
     * @param Component          $node
     * @param Exec_Interface_ValueProvider $valueProvider
     *
     * @return array
     */
    protected function executeComponent(Component $node, Exec_Interface_ValueProvider $valueProvider)
    {
        $results = array();

        if ($node instanceof  Leaf) {
            $results[$node->getName()] = $valueProvider->getValueForExecution($node->getName());
        } else if ($node instanceof Composite) {
            if ($valueProvider->getValueForExecution($node->getModifier()) === true) {
                foreach ($node->getChildren() as $child) {
                    $results = array_merge($results, $this->executeComponent($child, $valueProvider));
                }
            }
        }

        return $results;
    }

    /**
     * Retourne les feuilles qui seront exécutées en interprétant les conditions
     *
     * @param Exec_Interface_ValueProvider $valueProvider
     *
     * @return array Tableau des noms des feuilles exécutées
     */
    public function getSelectedLeafs(Exec_Interface_ValueProvider $valueProvider)
    {
        $results = array();

        foreach ($this->expression->getRootNode()->getChildren() as $rootNode) {
            $results = array_merge($results, $this->getSelectedComponentLeafs($rootNode, $valueProvider));
        }

        return $results;
    }

    /**
     * Méthode récursive qui va parcourir l'arbre et renvoyer le résultat de son éxécution.
     *
     * @param Component          $node
     * @param Exec_Interface_ValueProvider $valueProvider
     *
     * @return array Tableau des noms des feuilles exécutées
     */
    protected function getSelectedComponentLeafs(Component $node, Exec_Interface_ValueProvider $valueProvider)
    {
        $results = array();

        if ($node instanceof  Leaf) {
            $results[] = $node->getName();
        } else if ($node instanceof Composite) {
            if ($valueProvider->getValueForExecution($node->getModifier()) === true) {
                foreach ($node->getChildren() as $child) {
                    $results = array_merge($results, $this->getSelectedComponentLeafs($child, $valueProvider));
                }
            }
        }

        return $results;
    }

}
