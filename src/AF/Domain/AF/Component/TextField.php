<?php

namespace AF\Domain\AF\Component;

use AF\Domain\AF\InputSet\InputSet;
use AF\Domain\AF\GenerationHelper;
use AF\Domain\AF\Input\TextFieldInput;
use UI_Form_Element_Text;
use UI_Form_Element_Textarea;

/**
 * Gestion des champs de type texte.
 *
 * @author     matthieu.napoli
 * @author     hugo.charbonnier
 * @author     thibaud.rolland
 */
class TextField extends Field
{
    const TYPE_SHORT = 1;
    const TYPE_LONG = 2;

    protected $type = self::TYPE_SHORT;

    /**
     * Indique si le champ est requis ou non.
     * @var bool
     */
    protected $required = true;


    /**
     * @param int $type
     */
    public function __construct($type)
    {
        $this->setType($type);
    }

    /**
     * {@inheritdoc}
     */
    public function getUIElement(GenerationHelper $generationHelper)
    {
        if ($this->type == self::TYPE_SHORT) {
            $uiElement = new UI_Form_Element_Text($this->ref);
        } else {
            $uiElement = new UI_Form_Element_Textarea($this->ref);
        }
        $uiElement->setLabel($this->label);
        $uiElement->getElement()->help = $this->help;
        $uiElement->setRequired($this->getRequired());
        if ($generationHelper->isReadOnly()) {
            $uiElement->getElement()->setReadOnly();
        }
        // Remplit avec la valeur saisie
        $input = null;
        if ($generationHelper->getInputSet()) {
            /** @var $input TextFieldInput */
            $input = $generationHelper->getInputSet()->getInputForComponent($this);
        }
        if ($input) {
            $uiElement->getElement()->disabled = $input->isDisabled();
            $uiElement->getElement()->hidden = $input->isHidden();
            $uiElement->setValue($input->getValue());
            // Historique de la valeur
            $uiElement->getElement()->addElement($this->getHistoryComponent($input));
        } else {
            $uiElement->getElement()->disabled = !$this->enabled;
            $uiElement->getElement()->hidden = !$this->visible;
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
    public function getNbRequiredFields(InputSet $inputSet = null)
    {
        if (!$this->getRequired()) {
            return 0;
        }

        if ($inputSet) {
            $input = $inputSet->getInputForComponent($this);
            // Si la saisie est cachée : 0 champs requis
            if ($input && $input->isHidden()) {
                return 0;
            }
        }

        return 1;
    }

    /**
     * @return bool
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * @param bool $required
     */
    public function setRequired($required)
    {
        $this->required = (bool) $required;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}
