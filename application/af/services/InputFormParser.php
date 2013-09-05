<?php

use Unit\UnitAPI;

/**
 * Service responsable de parser le formulaire soumis
 *
 * @author  matthieu.napoli
 */
class AF_Service_InputFormParser
{

    /**
     * Parse la soumission d'un AF pour remplir un InputSet
     *
     * @param array       $formContent Contenu du post du formulaire
     * @param AF_Model_AF $af
     * @param array       $errorMessages
     *
     * @return AF_Model_InputSet_Primary
     */
    public function parseForm(array $formContent, AF_Model_AF $af, array & $errorMessages)
    {
        $inputSet = new AF_Model_InputSet_Primary($af);

        foreach ($formContent as $fullRef => $inputContent) {
            $refComponents = explode(UI_Generic::REF_SEPARATOR, $fullRef);
            $ref = $refComponents[count($refComponents) - 1];
            $number = null;

            // Si le ref est un numéro de sous-af répété
            if (is_numeric($ref)) {
                $ref = $refComponents[count($refComponents) - 2];
            }

            // Free label
            if ($ref == 'freeLabel') {
                continue;
            }

            try {
                $afComponent = AF_Model_Component::loadByRef($ref, $af);
            } catch (Core_Exception_NotFound $e) {
                // Ça n'est pas un composant de l'AF
                continue;
            }

            // Récupère l'Input
            $errorMessages += $this->createInputFromComponent($fullRef, $afComponent, $inputSet, $inputContent);

            // Groupe
            if ($afComponent instanceof AF_Model_Component_Group) {
                $errorMessages += $this->parseForm($inputContent['elements'], $inputSet, $af);
            }
        }

        return $inputSet;
    }

    /**
     * @param string             $fullRef Le ref du champ du formulaire (avec les préfixes)
     * @param AF_Model_Component $component
     * @param AF_Model_InputSet  $inputSet
     * @param array              $inputContent
     *
     * @throws InvalidArgumentException
     * @return array Error messages indexed by the field name
     */
    private function createInputFromComponent($fullRef, AF_Model_Component $component, AF_Model_InputSet $inputSet,
        array $inputContent
    ) {
        $errorMessages = [];

        if ($component instanceof AF_Model_Component_Group) {
            // Groupe
            $input = new AF_Model_Input_Group($inputSet, $component);

        } elseif ($component instanceof AF_Model_Component_Numeric) {
            // Champ numérique
            $input = new AF_Model_Input_Numeric($inputSet, $component);
            $locale = Core_Locale::loadDefault();
            $value = null;
            try {
                $value = $locale->readNumber($inputContent['value']);
                if ($component->getRequired() && $value === null) {
                    $errorMessages[$fullRef] = __('UI', 'formValidation', 'emptyRequiredField');
                }
            } catch (Core_Exception_InvalidArgument $e) {
                $errorMessages[$fullRef] = __('UI', 'formValidation', 'invalidNumber');
            }
            // Incertitude
            $relativeUncertainty = null;
            if ($component->getWithUncertainty()) {
                try {
                    $childInputContent = $inputContent['children']['percent' . $fullRef . '_child'];
                    $relativeUncertainty = $locale->readInteger($childInputContent['value']);
                    if ($relativeUncertainty < 0) {
                        $errorMessages[$fullRef] = __("UI", "formValidation", "invalidUncertainty");
                    }
                } catch (Core_Exception_InvalidArgument $e) {
                    $errorMessages[$fullRef] = __("UI", "formValidation", "invalidUncertainty");
                }
            }
            // Choix de l'unite
            $childUnitInputContent = $inputContent['children'][$fullRef . '_unit_child'];
            $selectedUnit = new UnitAPI($childUnitInputContent['value']);
            $input->setValue(
                new Calc_UnitValue($selectedUnit, $value, $relativeUncertainty)
            );

        } elseif ($component instanceof AF_Model_Component_Text) {
            // Champ texte
            $input = new AF_Model_Input_Text($inputSet, $component);
            if (!empty($inputContent['value'])) {
                $input->setValue($inputContent['value']);
            } elseif ($component->getRequired()) {
                $errorMessages[$fullRef] = __("UI", "formValidation", "emptyRequiredField");
            }

        } elseif ($component instanceof AF_Model_Component_Checkbox) {
            // Champ checkbox
            $input = new AF_Model_Input_Checkbox($inputSet, $component);
            if (isset($inputContent['value'])) {
                $input->setValue($inputContent['value']);
            }

        } elseif ($component instanceof AF_Model_Component_Select_Single) {
            // Champ de sélection simple
            $input = new AF_Model_Input_Select_Single($inputSet, $component);
            if (!empty($inputContent['value'])) {
                $option = $component->getOptionByRef($inputContent['value']);
                $input->setValue($option);
            } elseif ($component->getRequired()) {
                $errorMessages[$fullRef] = __("UI", "formValidation", "emptyRequiredField");
            }

        } elseif ($component instanceof AF_Model_Component_Select_Multi) {
            // Champ de sélection multiple
            $input = new AF_Model_Input_Select_Multi($inputSet, $component);
            if (!empty($inputContent['value']) && is_array($inputContent['value'])) {
                $options = [];
                foreach ($inputContent['value'] as $optionRef) {
                    $options[] = $component->getOptionByRef($optionRef);
                }
                $input->setValue($options);
            } elseif ($component->getRequired()) {
                $errorMessages[$fullRef] = __("UI", "formValidation", "emptyRequiredField");
            }

        } elseif ($component instanceof AF_Model_Component_SubAF_NotRepeated) {
            // Sous-AF non répété
            $input = new AF_Model_Input_SubAF_NotRepeated($inputSet, $component);
            $errorMessages += $this->parseForm($inputContent['elements'],
                $input->getValue(),
                $component->getCalledAF());

        } elseif ($component instanceof AF_Model_Component_SubAF_Repeated) {
            // Sous-AF répété
            $input = new AF_Model_Input_SubAF_Repeated($inputSet, $component);
            foreach ($inputContent['elements'] as $ref => $elements) {
                list($number) = explode(UI_Generic::REF_SEPARATOR, strrev($ref), 2);
                $number = strrev($number);
                if (is_numeric($number)) {
                    $subInputSets = $input->getValue();
                    if (isset($subInputSets[$number])) {
                        // La répétition existe déjà
                        $subInputSet = $subInputSets[$number];
                        // Free label
                        foreach ($elements as $subRef => $subInputContent) {
                            $refComponents = explode(UI_Generic::REF_SEPARATOR, $subRef);
                            if ($refComponents[count($refComponents) - 1] == 'freeLabel') {
                                $subInputSet->setFreeLabel($subInputContent['value']);
                                break;
                            }
                        }
                        $errorMessages += $this->parseForm($elements['elements'],
                            $subInputSet,
                            $component->getCalledAF());
                    } else {
                        // On crée une nouvelle répétition
                        $subInputSet = new AF_Model_InputSet_Sub($component->getCalledAF());
                        // Free label
                        foreach ($elements as $subRef => $subInputContent) {
                            $refComponents = explode(UI_Generic::REF_SEPARATOR, $subRef);
                            if ($refComponents[count($refComponents) - 1] == 'freeLabel') {
                                $subInputSet->setFreeLabel($subInputContent['value']);
                                break;
                            }
                        }
                        $errorMessages += $this->parseForm($elements,
                            $subInputSet,
                            $component->getCalledAF());
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
