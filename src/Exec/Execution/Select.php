<?php

namespace Exec\Execution;

use Exec\Execution;
use Exec\Provider\ValueInterface;
use TEC\Component\Component;
use TEC\Component\Composite;
use TEC\Component\Leaf;

/**
 * Exécute une expression de sélection.
 *
 * @author valentin.claras
 * @author matthieu.napoli
 */
class Select extends Execution
{
    /**
     * {@inheritdoc}
     */
    public function getErrors(ValueInterface $valueProvider)
    {
        return $this->getErrorsFromComponent($this->expression->getRootNode(), $valueProvider);
    }

    /**
     * {@inheritdoc}
     */
    protected function getErrorsFromComponent(Component $node, ValueInterface $valueProvider)
    {
        $errors = [];

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
     * {@inheritdoc}
     */
    public function executeExpression(ValueInterface $valueProvider)
    {
        $results = [];

        foreach ($this->expression->getRootNode()->getChildren() as $rootNode) {
            $results = array_merge($results, $this->executeComponent($rootNode, $valueProvider));
        }

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    protected function executeComponent(Component $node, ValueInterface $valueProvider)
    {
        $results = [];

        if ($node instanceof Leaf) {
            $results[$node->getName()] = $valueProvider->getValueForExecution(
                $node->getName(),
                ValueInterface::RESULT_STRING
            );
        } elseif ($node instanceof Composite) {
            if ($valueProvider->getValueForExecution($node->getModifier(), ValueInterface::RESULT_BOOL) === true) {
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
     * @param ValueInterface $valueProvider
     *
     * @return array Tableau des noms des feuilles exécutées
     */
    public function getSelectedLeafs(ValueInterface $valueProvider)
    {
        $results = [];

        foreach ($this->expression->getRootNode()->getChildren() as $rootNode) {
            $results = array_merge($results, $this->getSelectedComponentLeafs($rootNode, $valueProvider));
        }

        return $results;
    }

    /**
     * Méthode récursive qui va parcourir l'arbre et renvoyer le résultat de son éxécution.
     *
     * @param Component      $node
     * @param ValueInterface $valueProvider
     *
     * @return array Tableau des noms des feuilles exécutées
     */
    protected function getSelectedComponentLeafs(Component $node, ValueInterface $valueProvider)
    {
        $results = [];

        if ($node instanceof  Leaf) {
            $results[] = $node->getName();
        } elseif ($node instanceof Composite) {
            if ($valueProvider->getValueForExecution($node->getModifier(), ValueInterface::RESULT_BOOL) === true) {
                foreach ($node->getChildren() as $child) {
                    $results = array_merge($results, $this->getSelectedComponentLeafs($child, $valueProvider));
                }
            }
        }

        return $results;
    }
}
