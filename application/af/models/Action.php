<?php
/**
 * @author     matthieu.napoli
 * @author     hugo.charbonnier
 * @author     thibaud.rolland
 * @package    AF
 * @subpackage Form
 */

/**
 * Gestion des actions associées aux champs.
 * @package    AF
 * @subpackage Form
 */
abstract class AF_Model_Action extends Core_Model_Entity
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
     * @var AF_Model_Condition|null
     */
    protected $condition;

    /**
     * @var AF_Model_Component
     */
    protected $targetComponent;


    /**
     * Génère une action UI
     * @param AF_GenerationHelper $generationHelper
     * @return UI_Form_Action
     */
    abstract public function getUIAction(AF_GenerationHelper $generationHelper);

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the condition of the action
     * @return AF_Model_Condition
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * Set the condition for the action
     * @param AF_Model_Condition|null $condition
     */
    public function setCondition(AF_Model_Condition $condition = null)
    {
        $this->condition = $condition;
    }

    /**
     * @return AF_Model_Component
     */
    public function getTargetComponent()
    {
        return $this->targetComponent;
    }

    /**
     * @param AF_Model_Component $targetComponent
     */
    public function setTargetComponent(AF_Model_Component $targetComponent)
    {
        if ($this->targetComponent !== $targetComponent) {
            $this->targetComponent = $targetComponent;
            $targetComponent->addAction($this);
        }
    }

    /**
     * Méthode utilisée pour vérifier la configuration des actions.
     * @return AF_ConfigError[]
     */
    public function checkConfig()
    {
        $errors = [];
        // On vérifie que l'action est associée à une condition qui la déclenche
        if ($this->condition === null) {
            $configError = new AF_ConfigError();
            $configError->isFatal(true);
            $configError->setMessage("L'action dont l'identifiant est " . $this->id
                                         . " n'est associée à aucune condition valide.");
            $errors[] = $configError;
        }
        return $errors;
    }

}
