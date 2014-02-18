<?php

namespace AF\Application;

use AF\Domain\AF;
use AF\Domain\Component\Group;
use AF\Domain\Component\Select\SelectMulti;
use AF\Domain\Component\Select\SelectSingle;
use AF\Domain\Component\SubAF\NotRepeatedSubAF;
use AF\Domain\Component\TextField;
use AF\Domain\Component\NumericField;
use AF\Domain\Component\Checkbox;
use AF\Domain\Component\SubAF\RepeatedSubAF;
use AF\Domain\Input\TextFieldInput;
use AF\Domain\Input\NumericFieldInput;
use AF\Domain\Input\GroupInput;
use AF\Domain\Input\CheckboxInput;
use AF\Domain\Input\Select\SelectSingleInput;
use AF\Domain\Input\Select\SelectMultiInput;
use AF\Domain\Input\SubAF\RepeatedSubAFInput;
use AF\Domain\Input\SubAF\NotRepeatedSubAFInput;
use AF\Domain\InputSet\InputSet;
use AF\Domain\Component\Component;
use AF\Domain\InputSet\PrimaryInputSet;
use AF\Domain\InputSet\SubInputSet;
use Calc_UnitValue;
use Core_Exception_InvalidArgument;
use Core_Exception_NotFound;
use Core_Locale;
use Exception;
use InvalidArgumentException;
use UI_Generic;
use Unit\UnitAPI;

/**
 * Service responsable de parser le formulaire soumis.
 *
 * TODO à mettre à jour quand http://tasks.myc-sense.com/issues/6422 sera traitée
 *
 * @author matthieu.napoli
 */
class InputFormParser
{
    /**
     * Parse la soumission d'un AF pour remplir un InputSet
     *
     * @param array $formContent Contenu du post du formulaire
     * @param AF    $af
     * @param array $errorMessages
     *
     * @return PrimaryInputSet
     */
    public function parseForm(array $formContent, AF $af, array & $errorMessages)
    {
        $inputSet = new PrimaryInputSet($af);

        $errorMessages = $this->doParseForm($formContent, $inputSet, $af);

        return $inputSet;
    }

    /**
     * Parse la soumission d'un AF pour remplir un InputSet
     *
     * @param array    $formContent Contenu du post du formulaire
     * @param InputSet $inputSet
     * @param AF       $af
     *
     * @return array
     */
    public function doParseForm(array $formContent, InputSet $inputSet, AF $af)
    {
        $errorMessages = [];

        foreach ($formContent as $fullRef => $inputContent) {
            $refComponents = explode(UI_Generic::REF_SEPARATOR, $fullRef);
            $ref = $refComponents[count($refComponents) - 1];

            // Si le ref est un numéro de sous-af répété
            if (is_numeric($ref)) {
                $ref = $refComponents[count($refComponents) - 2];
            }

            // Free label
            if ($ref == 'freeLabel') {
                continue;
            }

            try {
                $afComponent = Component::loadByRef($ref, $af);
            } catch (Core_Exception_NotFound $e) {
                // Ça n'est pas un composant de l'AF
                continue;
            }

            // Récupère l'Input
            $errorMessages += $this->createInputFromComponent($fullRef, $afComponent, $inputSet, $inputContent);

            // Groupe
            if ($afComponent instanceof Group) {
                $errorMessages += $this->doParseForm($inputContent['elements'], $inputSet, $af);
            }
        }

        return $errorMessages;
    }

    /**
     * @param string    $fullRef Le ref du champ du formulaire (avec les préfixes)
     * @param Component $component
     * @param InputSet  $inputSet
     * @param array     $inputContent
     *
     * @throws InvalidArgumentException
     * @throws Exception
     * @return array Error messages indexed by the field name
     */
    private function createInputFromComponent(
        $fullRef,
        Component $component,
        InputSet $inputSet,
        array $inputContent
    ) {
        $errorMessages = [];

        if ($component instanceof Group) {
            // Groupe
            $input = new GroupInput($inputSet, $component);

        } elseif ($component instanceof NumericField) {
            // Champ numérique
            $input = new NumericFieldInput($inputSet, $component);
            $locale = Core_Locale::loadDefault();
            $value = null;
            try {
                $value = $locale->readNumber($inputContent['value']);
                if ($component->getRequired() && $value === null) {
                    $errorMessages[$fullRef] = __('AF', 'inputInput', 'emptyRequiredField');
                }
            } catch (Core_Exception_InvalidArgument $e) {
                $errorMessages[$fullRef] = __('UI', 'formValidation', 'invalidNumber');
            }
            // Incertitude
            if ($component->getWithUncertainty()) {
                try {
                    $relativeUncertainty = null;
                    // TODO à virer une fois tasks.myc-sense.com/issues/6422
                    foreach ($inputContent['children'] as $key => $childInputContent) {
                        if (strpos($key, 'percent') !== false) {
                            $relativeUncertainty = $locale->readInteger($childInputContent['value']);
                            if ($relativeUncertainty < 0) {
                                $errorMessages[$fullRef] = __("UI", "formValidation", "invalidUncertainty");
                                $relativeUncertainty = null;
                            }
                            break;
                        }
                    }
                } catch (Core_Exception_InvalidArgument $e) {
                    $errorMessages[$fullRef] = __("UI", "formValidation", "invalidUncertainty");
                    $relativeUncertainty = null;
                }
            } else {
                $relativeUncertainty = null;
            }
            // Choix de l'unite
            // TODO à virer une fois tasks.myc-sense.com/issues/6422
            foreach ($inputContent['children'] as $key => $childUnitInputContent) {
                if (strpos($key, '_unit_') !== false) {
                    $selectedUnit = new UnitAPI($childUnitInputContent['value']);
                    break;
                }
            }
            if (!isset($selectedUnit)) {
                throw new Exception("Il est temps de refactoriser un peu le parsing des soumissions de UI_Form");
            }
            $input->setValue(
                new Calc_UnitValue($selectedUnit, $value, $relativeUncertainty)
            );

        } elseif ($component instanceof TextField) {
            // Champ texte
            $input = new TextFieldInput($inputSet, $component);
            if (!empty($inputContent['value'])) {
                $input->setValue($inputContent['value']);
            } elseif ($component->getRequired()) {
                $errorMessages[$fullRef] = __("AF", "inputInput", "emptyRequiredField");
            }

        } elseif ($component instanceof Checkbox) {
            // Champ checkbox
            $input = new CheckboxInput($inputSet, $component);
            if (isset($inputContent['value'])) {
                $input->setValue($inputContent['value']);
            }

        } elseif ($component instanceof SelectSingle) {
            // Champ de sélection simple
            $input = new SelectSingleInput($inputSet, $component);
            if (!empty($inputContent['value'])) {
                $option = $component->getOptionByRef($inputContent['value']);
                $input->setValue($option);
            } elseif ($component->getRequired()) {
                $errorMessages[$fullRef] = __("AF", "inputInput", "emptyRequiredField");
            }

        } elseif ($component instanceof SelectMulti) {
            // Champ de sélection multiple
            $input = new SelectMultiInput($inputSet, $component);
            if (!empty($inputContent['value']) && is_array($inputContent['value'])) {
                $options = [];
                foreach ($inputContent['value'] as $optionRef) {
                    $options[] = $component->getOptionByRef($optionRef);
                }
                $input->setValue($options);
            } elseif ($component->getRequired()) {
                $errorMessages[$fullRef] = __("AF", "inputInput", "emptyRequiredField");
            }

        } elseif ($component instanceof NotRepeatedSubAF) {
            // Sous-AF non répété
            $input = new NotRepeatedSubAFInput($inputSet, $component);
            $errorMessages += $this->doParseForm(
                $inputContent['elements'],
                $input->getValue(),
                $component->getCalledAF()
            );

        } elseif ($component instanceof RepeatedSubAF) {
            // Sous-AF répété
            $input = new RepeatedSubAFInput($inputSet, $component);
            foreach ($inputContent['elements'] as $ref => $elements) {
                list($repetitionNumber) = explode(UI_Generic::REF_SEPARATOR, strrev($ref), 2);
                $repetitionNumber = strrev($repetitionNumber);
                if (is_numeric($repetitionNumber)) {
                    $subInputSets = $input->getValue();
                    if (isset($subInputSets[$repetitionNumber])) {
                        // La répétition existe déjà
                        $subInputSet = $subInputSets[$repetitionNumber];
                        // Free label
                        foreach ($elements as $subRef => $subInputContent) {
                            $refComponents = explode(UI_Generic::REF_SEPARATOR, $subRef);
                            if ($refComponents[count($refComponents) - 1] == 'freeLabel') {
                                $subInputSet->setFreeLabel($subInputContent['value']);
                                break;
                            }
                        }
                        $errorMessages += $this->doParseForm(
                            $elements['elements'],
                            $subInputSet,
                            $component->getCalledAF()
                        );
                    } else {
                        // On crée une nouvelle répétition
                        $subInputSet = new SubInputSet($component->getCalledAF());
                        // Free label
                        foreach ($elements as $subRef => $subInputContent) {
                            $refComponents = explode(UI_Generic::REF_SEPARATOR, $subRef);
                            if ($refComponents[count($refComponents) - 2] == 'freeLabel') {
                                $subInputSet->setFreeLabel($subInputContent['value']);
                                break;
                            }
                        }
                        $errorMessages += $this->doParseForm(
                            $elements,
                            $subInputSet,
                            $component->getCalledAF()
                        );
                        $input->addSubSet($subInputSet);
                    }
                }
            }
        } else {
            throw new InvalidArgumentException("Unknown component " . get_class($component));
        }

        // On enregistre les éléments communs à tout type d'élément
        if (isset($inputContent['hidden'])) {
            $input->setHidden($inputContent['hidden']);
        }
        if (isset($inputContent['disabled'])) {
            $input->setDisabled($inputContent['disabled']);
        }

        return $errorMessages;
    }
}
