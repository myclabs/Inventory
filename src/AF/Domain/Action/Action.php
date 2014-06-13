<?php

namespace AF\Domain\Action;

use Core_Model_Entity;
use AF\Domain\Condition\Condition;
use AF\Domain\AFConfigurationError;
use AF\Domain\Component\Component;

/**
 * Gestion des actions associées aux champs.
 *
 * @author matthieu.napoli
 * @author hugo.charbonnier
 * @author thibaud.rolland
 */
abstract class Action extends Core_Model_Entity
{
    const QUERY_CONDITION = 'condition';

    const TYPE_SETVALUE = 1;
    const TYPE_SETALGOVALUE = 2;
    const TYPE_SETSTATE = 3;
    const TYPE_HIDE = 3;
    const TYPE_SHOW = 4;
    const TYPE_ENABLE = 5;
    const TYPE_DISABLE = 6;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var Condition|null
     */
    protected $condition;

    /**
     * @var Component
     */
    protected $targetComponent;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the condition of the action
     * @return Condition
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * Set the condition for the action
     * @param Condition|null $condition
     */
    public function setCondition(Condition $condition = null)
    {
        $this->condition = $condition;
    }

    /**
     * @return Component
     */
    public function getTargetComponent()
    {
        return $this->targetComponent;
    }

    /**
     * @param Component $targetComponent
     */
    public function setTargetComponent(Component $targetComponent)
    {
        if ($this->targetComponent !== $targetComponent) {
            $this->targetComponent = $targetComponent;
            $targetComponent->addAction($this);
        }
    }

    /**
     * Méthode utilisée pour vérifier la configuration des actions.
     * @return AFConfigurationError[]
     */
    public function checkConfig()
    {
        $errors = [];
        // On vérifie que l'action est associée à une condition qui la déclenche
        if ($this->condition === null) {
            $configError = new AFConfigurationError();
            $configError->isFatal(true);
            $configError->setMessage(
                "L'action dont l'identifiant est " . $this->id
                . " n'est associée à aucune condition valide."
            );
            $errors[] = $configError;
        }
        return $errors;
    }
}
