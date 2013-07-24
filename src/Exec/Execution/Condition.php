<?php
/**
 * @author valentin.claras
 * @author yoann.croizer
 * @author hugo.charbonnier
 * @package Exec
 */

use TEC\Component\Component;
use TEC\Component\Composite;
use TEC\Component\Leaf;

/**
 * classe Exec_Execution_Condition
 * @package Exec
 * @subpackage Execution
 */
class Exec_Execution_Condition extends Exec_Execution
{
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
        } else if ($node instanceof Composite) {
            foreach ($node->getChildren() as $child) {
                $errors = array_merge($errors, $this->getErrorsFromComponent($child, $valueProvider));
            }
        }

        return $errors;
    }

    /**
     * Méthode récursive qui va parcourir l'arbre et renvoyer le résultat de son éxécution.
     *
     * @param Component          $node
     * @param Exec_Interface_ValueProvider $valueProvider
     *
     * @return bool
     */
    protected  function executeComponent(Component $node, Exec_Interface_ValueProvider $valueProvider)
    {
        $result = null;

        if ($node instanceof Leaf) {
            $result = (bool) $valueProvider->getValueForExecution($node->getName());
        } else if ($node instanceof  Composite) {
            foreach ($node->getChildren() as $child) {
                $childResult = $this->executeComponent($child, $valueProvider);
                if ($result == null) {
                    $result = $childResult;
                } else {
                    switch ($node->getOperator()) {
                        case Composite::LOGICAL_AND:
                            $result &= $childResult;
                            break;
                        case Composite::LOGICAL_OR:
                            $result |= $childResult;
                            break;
                        case Composite::LOGICAL_XOR:
                            $result ^= $childResult;
                            break;
                    }
                    $result = (bool) $result;
                }
            }
        }

        return $result;
    }

}
