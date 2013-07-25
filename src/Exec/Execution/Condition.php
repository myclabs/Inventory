<?php
/**
 * @author     valentin.claras
 * @package    Exec
 * @subpackage Execution
 */

namespace Exec\Execution;

use Exec\Execution;
use Exec\Provider\ValueInterface;
use TEC\Component\Component;
use TEC\Component\Composite;
use TEC\Component\Leaf;

/**
 * classe Condition
 * @package    Exec
 * @subpackage Execution
 */
class Condition extends Execution
{
    /**
     * Méthode récursive qui va parcourir l'arbre et vérifier les composants pour son éxécution.
     *
     * @param Component $node
     * @param \Exec\Provider\ValueInterface $valueProvider
     *
     * @return array
     */
    protected function getErrorsFromComponent(Component $node, ValueInterface $valueProvider)
    {
        $errors = array();

        if ($node instanceof Leaf) {
            $errors = array_merge($errors, $valueProvider->checkValueForExecution($node->getName()));
        } else {
            if ($node instanceof Composite) {
                foreach ($node->getChildren() as $child) {
                    $errors = array_merge($errors, $this->getErrorsFromComponent($child, $valueProvider));
                }
            }
        }

        return $errors;
    }

    /**
     * Méthode récursive qui va parcourir l'arbre et renvoyer le résultat de son éxécution.
     *
     * @param Component $node
     * @param \Exec\Provider\ValueInterface $valueProvider
     *
     * @return bool
     */
    protected function executeComponent(Component $node, ValueInterface $valueProvider)
    {
        $result = null;

        if ($node instanceof Leaf) {
            $result = (bool)$valueProvider->getValueForExecution($node->getName());
        } else {
            if ($node instanceof  Composite) {
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
                        $result = (bool)$result;
                    }
                }
            }
        }

        return $result;
    }

}
