<?php
/**
 * @author     matthieu.napoli
 * @author     yoann.croizer
 * @package    AF
 * @subpackage Form
 */

/**
 * @package    AF
 * @subpackage Form
 */
class AF_Model_Component_Select_Single extends AF_Model_Component_Select
{

    /**
     * Constante associée à l'attribut 'style'.
     * Correspond à une liste déroulante à choix unique.
     * @var int
     */
    const TYPE_LIST = 1;

    /**
     * Constante associée à l'attribut 'style'.
     * Correspond à un bouton radio à choix unique.
     * @var int
     */
    const TYPE_RADIO = 2;

    /**
     * Identifiant of the default option selectioned.
     * @var AF_Model_Component_Select_Option
     */
    protected $defaultValue;

    /**
     * Indicate if the field is a scroll list or a radioList.
     * @var int
     */
    protected $type = self::TYPE_LIST;


    /**
     * {@inheritdoc}
     */
    public function getUIElement(AF_GenerationHelper $generationHelper)
    {
        switch ($this->type) {
            case self::TYPE_RADIO :
                $uiElement = new UI_Form_Element_Radio($this->ref);
                break;
            case self::TYPE_LIST :
                $uiElement = new UI_Form_Element_Select($this->ref);
                // Ajout d'un choix vide à la liste déroulante
                $uiElement->addNullOption('');
                break;
            default:
                throw new Core_Exception_UndefinedAttribute("The type must be defined and valid");
        }
        $uiElement->setLabel($this->label);
        $uiElement->getElement()->help = $this->help;
        $uiElement->setRequired($this->getRequired());
        // Liste des options
        foreach ($this->options as $option) {
            $uiElement->addOption($generationHelper->getUIOption($option));
        }
        if ($generationHelper->isReadOnly()) {
            $uiElement->getElement()->setReadOnly();
        }
        // Remplit avec la valeur saisie
        $input = null;
        if ($generationHelper->getInputSet()) {
            /** @var $input AF_Model_Input_Select_Single */
            $input = $generationHelper->getInputSet()->getInputForComponent($this);
        }
        if ($input) {
            $uiElement->getElement()->disabled = $input->isDisabled();
            $uiElement->getElement()->hidden = $input->isHidden();
            $optionRef = $input->getValue();
            if ($optionRef) {
                $uiElement->setValue($optionRef);
            }
            // Historique de la valeur
            $uiElement->getElement()->addElement($this->getHistoryComponent($input));
        } else {
            $uiElement->getElement()->disabled = !$this->enabled;
            $uiElement->getElement()->hidden = !$this->visible;
            // Valeur par défaut
            if ($this->defaultValue) {
                $uiElement->setValue($this->defaultValue->getRef());
            }
        }
        // Actions
        foreach ($this->actions as $action) {
            $uiElement->getElement()->addAction($generationHelper->getUIAction($action));
        }
        return $uiElement;
    }

    /**
     * {@inheritdoc}
     */
    public function setRef($ref)
    {
        $oldRef = $this->ref;
        parent::setRef($ref);
        // Modifie également le ref de l'algo associé
        try {
            $af = $this->getAf();
            if ($af) {
                $algo = $af->getAlgoByRef($oldRef);
                if ($algo instanceof Algo_Model_Selection_TextKey_Input) {
                    $algo->setInputRef($ref);
                    $algo->setRef($ref);
                    $algo->save();
                }
            }
        } catch (Core_Exception_NotFound $e) {
        }
    }

    /**
     * @return AF_Model_Component_Select_Option|null
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param AF_Model_Component_Select_Option|null $defaultValue
     */
    public function setDefaultValue(AF_Model_Component_Select_Option $defaultValue = null)
    {
        $this->defaultValue = $defaultValue;
    }

    /**
     * Get the style attribute.
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the style attribute.
     * @param ?int? $style
     */
    public function setType($style)
    {
        $this->type = $style;
    }
}
