<?php

namespace AF\Domain\Action;

use AF\Domain\AFGenerationHelper;
use Core_Model_Entity;
use AF\Domain\Condition\Condition;
use AF\Domain\AFConfigurationError;
use UI_Form_Action;
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
     * @var \AF\Domain\Condition\Condition|null
     */
    protected $condition;

    /**
     * @var \AF\Domain\Component\Component
     */
    protected $targetComponent;


    /**
     * Génère une action UI
     * @param AFGenerationHelper $generationHelper
     * @return UI_Form_Action
     */
    abstract public function getUIAction(AFGenerationHelper $generationHelper);

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the condition of the action
     * @return \AF\Domain\Condition\Condition
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * Set the condition for the action
     * @param \AF\Domain\Condition\Condition|null $condition
     */
    public function setCondition(Condition $condition = null)
    {
        $this->condition = $condition;
    }

    /**
     * @return \AF\Domain\Component\Component
     */
    public function getTargetComponent()
    {
        return $this->targetComponent;
    }

    /**
     * @param \AF\Domain\Component\Component $targetComponent
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
