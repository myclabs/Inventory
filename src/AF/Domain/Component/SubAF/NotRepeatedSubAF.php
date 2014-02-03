<?php

namespace AF\Domain\Component\SubAF;

use AF\Domain\InputSet\InputSet;
use AF\Domain\AFGenerationHelper;
use AF\Domain\Component\SubAF;
use AF\Domain\Input\SubAF\NotRepeatedSubAFInput;
use UI_Form_Element_Group;

/**
 * Gestion des sous-formulaires non répétés.
 *
 * @author matthieu.napoli
 * @author yoann.croizer
 */
class NotRepeatedSubAF extends SubAF
{
    /**
     * {@inheritdoc}
     */
    public function getUIElement(AFGenerationHelper $generationHelper)
    {
        $uiElement = new UI_Form_Element_Group($this->ref);
        $uiElement->setLabel($this->label);
        $uiElement->getElement()->help = $this->help;
        $uiElement->getElement()->hidden = !$this->visible;
        switch ($this->foldaway) {
            case self::FOLDAWAY:
                $uiElement->foldaway = true;
                break;
            case self::FOLDED:
                $uiElement->folded = true;
                break;
            default:
                $uiElement->foldaway = false;
        }
        // Récupère la saisie correspondant à cet élément
        $input = null;
        if ($generationHelper->getInputSet()) {
            /** @var $input \AF\Domain\Input\SubAF\NotRepeatedSubAFInput */
            $input = $generationHelper->getInputSet()->getInputForComponent($this);
            if ($input) {
                $uiElement->getElement()->hidden = $input->isHidden();
                $uiElement->getElement()->disabled = $input->isDisabled();
            }
        }
        // Récupère le sous-inputSet correspondant à ce sous-af
        $subInputSet = null;
        if ($input) {
            $subInputSet = $input->getValue();
        }

        // Sous-formulaire
        $subForm = $this->calledAF->generateSubForm($generationHelper, $subInputSet);
        $subForm->getElement()->prefixRef($this->ref);
        $uiElement->addElement($subForm);
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
        // Si il y'a une saisie
        if ($inputSet) {
            /** @var $input \AF\Domain\Input\SubAF\NotRepeatedSubAFInput */
            $input = $inputSet->getInputForComponent($this);
            if ($input) {
                if ($input->isHidden()) {
                    return 0;
                }
                $subInputSet = $input->getValue();
                return $this->getCalledAF()->getNbRequiredFields($subInputSet);
            }
        }
        // Pas de saisie
        return $this->getCalledAF()->getNbRequiredFields();
    }
}
