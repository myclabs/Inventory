<?php

namespace Exec\Execution;

use Exec\Execution;
use Exec\Provider\ValueInterface;
use TEC\Component\Component;
use TEC\Component\Composite;
use TEC\Component\Leaf;

/**
 * Exécute une expression d'opération sur des conditions.
 *
 * @author valentin.claras
 */
class Condition extends Execution
{
    /**
     * {@inheritdoc}
     */
    protected function getErrorsFromComponent(Component $node, ValueInterface $valueProvider)
    {
        $errors = [];

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
     * {@inheritdoc}
     */
    protected function executeComponent(Component $node, ValueInterface $valueProvider)
    {
        $result = null;

        if ($node instanceof Leaf) {
            $result = (bool) $valueProvider->getValueForExecution($node->getName(), ValueInterface::RESULT_BOOL);
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
                        }
                        $result = (bool) $result;
                    }
                }
            }
        }

        return $result;
    }
}
