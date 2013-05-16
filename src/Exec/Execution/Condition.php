<?php
/**
 * @author valentin.claras
 * @author yoann.croizer
 * @author hugo.charbonnier
 * @package Exec
 */

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
     * @param TEC_Model_Component          $node
     * @param Exec_Interface_ValueProvider $valueProvider
     *
     * @return array
     */
    protected function getErrorsFromComponent(TEC_Model_Component $node, Exec_Interface_ValueProvider $valueProvider)
    {
        $errors = array();

        if ($node instanceof TEC_Model_Leaf) {
            $errors = array_merge($errors, $valueProvider->checkValueForExecution($node->getName()));
        } else if ($node instanceof TEC_Model_Composite) {
            foreach ($node->getChildren() as $child) {
                $errors = array_merge($errors, $this->getErrorsFromComponent($child, $valueProvider));
            }
        }

        return $errors;
    }

    /**
     * Méthode récursive qui va parcourir l'arbre et renvoyer le résultat de son éxécution.
     *
     * @param TEC_Model_Component          $node
     * @param Exec_Interface_ValueProvider $valueProvider
     *
     * @return bool
     */
    protected  function executeComponent(TEC_Model_Component $node, Exec_Interface_ValueProvider $valueProvider)
    {
        $result = null;

        if ($node instanceof TEC_Model_Leaf) {
            $result = (bool) $valueProvider->getValueForExecution($node->getName());
        } else if ($node instanceof  TEC_Model_Composite) {
            foreach ($node->getChildren() as $child) {
                $childResult = $this->executeComponent($child, $valueProvider);
                if ($result == null) {
                    $result = $childResult;
                } else {
                    switch ($node->getOperator()) {
                        case TEC_Model_Composite::LOGICAL_AND:
                            $result &= $childResult;
                            break;
                        case TEC_Model_Composite::LOGICAL_OR:
                            $result |= $childResult;
                            break;
                        case TEC_Model_Composite::LOGICAL_XOR:
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
