<?php

namespace AF\Domain\Component;

use AF\Domain\InputSet\InputSet;
use AF\Domain\AFGenerationHelper;
use AF\Domain\Input\TextFieldInput;
use AF\Application\Form\Element\TextField as FormTextField;
use AF\Application\Form\Element\Textarea;

/**
 * Gestion des champs de type texte.
 *
 * @author matthieu.napoli
 * @author hugo.charbonnier
 * @author thibaud.rolland
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
        parent::__construct();
        $this->setType($type);
    }

    /**
     * {@inheritdoc}
     */
    public function getUIElement(AFGenerationHelper $generationHelper)
    {
        if ($this->type == self::TYPE_SHORT) {
            $uiElement = new FormTextField($this->ref);
        } else {
            $uiElement = new Textarea($this->ref);
        }
        $uiElement->setLabel($this->uglyTranslate($this->label));
        $uiElement->getElement()->help = $this->uglyTranslate($this->help);
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
    public function initializeNewInput(InputSet $inputSet)
    {
        $input = $inputSet->getInputForComponent($this);

        if ($input === null) {
            $input = new TextFieldInput($inputSet, $this);
            $inputSet->setInputForComponent($this, $input);
        }
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
