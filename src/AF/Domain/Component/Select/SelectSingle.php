<?php

namespace AF\Domain\Component\Select;

use AF\Domain\Input\Select\SelectSingleInput;
use UI_Form_Element_Select;
use AF\Domain\Algorithm\Selection\TextKey\InputSelectionAlgo;
use AF\Domain\AFGenerationHelper;
use Core_Exception_NotFound;
use Core_Exception_UndefinedAttribute;
use AF\Domain\Component\Select;
use UI_Form_Element_Radio;

/**
 * @author matthieu.napoli
 * @author yoann.croizer
 */
class SelectSingle extends Select
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
     * @var SelectOption
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
    public function getUIElement(AFGenerationHelper $generationHelper)
    {
        switch ($this->type) {
            case self::TYPE_RADIO:
                $uiElement = new UI_Form_Element_Radio($this->ref);
                break;
            case self::TYPE_LIST:
                $uiElement = new UI_Form_Element_Select($this->ref);
                // Ajout d'un choix vide à la liste déroulante
                $uiElement->addNullOption('');
                break;
            default:
                throw new Core_Exception_UndefinedAttribute("The type must be defined and valid");
        }
        $uiElement->setLabel($this->uglyTranslate($this->label));
        $uiElement->getElement()->help = $this->uglyTranslate($this->help);
        $uiElement->setRequired($this->getRequired());
        // Liste des options
        foreach ($this->options as $option) {
            $uiElement->addOption($generationHelper->getUIOption($option));
        }
        if ($generationHelper->isReadOnly()) {
            $uiElement->getElement()->setReadOnly(true);
        }
        // Remplit avec la valeur saisie
        $input = null;
        if ($generationHelper->getInputSet()) {
            /** @var $input SelectSingleInput */
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
                if ($algo instanceof InputSelectionAlgo) {
                    $algo->setInputRef($ref);
                    $algo->setRef($ref);
                    $algo->save();
                }
            }
        } catch (Core_Exception_NotFound $e) {
        }
    }

    /**
     * @return SelectOption|null
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param SelectOption|null $defaultValue
     */
    public function setDefaultValue(SelectOption $defaultValue = null)
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
     * @param int $style
     */
    public function setType($style)
    {
        $this->type = $style;
    }
}
