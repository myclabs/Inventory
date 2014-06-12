<?php

namespace AF\Domain\Component\SubAF;

use AF\Domain\InputSet\InputSet;
use AF\Domain\Component\SubAF;
use AF\Domain\Input\SubAF\NotRepeatedSubAFInput;

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
    public function initializeNewInput(InputSet $inputSet)
    {
        $input = $inputSet->getInputForComponent($this);

        if ($input === null) {
            $input = new NotRepeatedSubAFInput($inputSet, $this);
            $inputSet->setInputForComponent($this, $input);
        }

        $this->calledAF->initializeNewInput($input->getValue());
    }

    /**
     * {@inheritdoc}
     */
    public function getNbRequiredFields(InputSet $inputSet = null)
    {
        // Si il y'a une saisie
        if ($inputSet) {
            /** @var $input NotRepeatedSubAFInput */
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
