<?php

namespace AF\Architecture\Service;

use AF\Domain\Action\Action;
use AF\Domain\Action\SetState;
use AF\Domain\Action\SetValue;
use AF\Domain\AF;
use AF\Domain\Component\Checkbox;
use AF\Domain\Component\Component;
use AF\Domain\Component\Group;
use AF\Domain\Component\NumericField;
use AF\Domain\Component\Select\SelectMulti;
use AF\Domain\Component\Select\SelectSingle;
use AF\Domain\Component\SubAF\NotRepeatedSubAF;
use AF\Domain\Component\SubAF\RepeatedSubAF;
use AF\Domain\Component\TextField;
use AF\Domain\Condition\CheckboxCondition;
use AF\Domain\Condition\Condition;
use AF\Domain\Condition\ElementaryCondition;
use AF\Domain\Condition\Select\SelectSingleCondition;
use AF\Domain\Input\CheckboxInput;
use AF\Domain\Input\Input;
use AF\Domain\Input\NumericFieldInput;
use AF\Domain\Input\Select\SelectMultiInput;
use AF\Domain\Input\Select\SelectSingleInput;
use AF\Domain\Input\SubAF\NotRepeatedSubAFInput;
use AF\Domain\Input\SubAF\RepeatedSubAFInput;
use AF\Domain\Input\TextFieldInput;
use AF\Domain\InputSet\InputSet;
use AF\Domain\InputSet\PrimaryInputSet;
use AF\Domain\InputSet\SubInputSet;
use Calc_UnitValue;
use Core_Exception_InvalidArgument;
use Core_Locale;
use Mnapoli\Translated\Translator;
use Unit\UnitAPI;

/**
 * Service permettant de serializer/déserializer une saisie.
 *
 * @author matthieu.napoli
 */
class InputSerializer
{
    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param array $data
     * @param AF    $af
     * @return PrimaryInputSet
     */
    public function unserialize(array $data, AF $af)
    {
        $inputSet = new PrimaryInputSet($af);

        foreach ($data['inputs'] as $inputData) {
            $this->unserializeInput($inputData, $inputSet, $af);
        }

        return $inputSet;
    }

    private function unserializeInput($data, InputSet $inputSet, AF $af)
    {
        $component = Component::loadByRef($data['componentRef'], $af);

        if ($component instanceof NumericField) {
            // Champ numérique
            $input = new NumericFieldInput($inputSet, $component);
            $locale = Core_Locale::loadDefault();
            $value = null;
            try {
                if (isset($data['value']['digitalValue'])) {
                    $value = $locale->readNumber($data['value']['digitalValue']);
                }
            } catch (Core_Exception_InvalidArgument $e) {
            }
            $relativeUncertainty = null;
            if ($component->getWithUncertainty()) {
                try {
                    if (isset($data['value']['uncertainty'])) {
                        $relativeUncertainty = $locale->readInteger($data['value']['uncertainty']);
                    }
                } catch (Core_Exception_InvalidArgument $e) {
                }
            }
            // Choix de l'unite
            if ($component->hasUnitSelection() && isset($data['value']['unit'])) {
                $unit = new UnitAPI($data['value']['unit']);
            } else {
                $unit = $component->getUnit();
            }
            $input->setValue(new Calc_UnitValue($unit, $value, $relativeUncertainty));

        } elseif ($component instanceof TextField) {
            // Champ texte
            $input = new TextFieldInput($inputSet, $component);
            if (!empty($data['value'])) {
                $input->setValue($data['value']);
            }

        } elseif ($component instanceof Checkbox) {
            // Champ checkbox
            $input = new CheckboxInput($inputSet, $component);
            if (isset($data['value'])) {
                $input->setValue($data['value']);
            }

        } elseif ($component instanceof SelectSingle) {
            // Champ de sélection simple
            $input = new SelectSingleInput($inputSet, $component);
            if (!empty($data['value'])) {
                $option = $component->getOptionByRef($data['value']);
                $input->setValue($option);
            }

        } elseif ($component instanceof SelectMulti) {
            // Champ de sélection multiple
            $input = new SelectMultiInput($inputSet, $component);
            if (!empty($data['value']) && is_array($data['value'])) {
                $options = [];
                foreach ($data['value'] as $optionRef) {
                    $options[] = $component->getOptionByRef($optionRef);
                }
                $input->setValue($options);
            }

        } elseif ($component instanceof NotRepeatedSubAF) {
            // Sous-AF non répété
            $input = new NotRepeatedSubAFInput($inputSet, $component);
            $this->doParseForm(
                $data['elements'],
                $input->getValue(),
                $component->getCalledAF()
            );

        } elseif ($component instanceof RepeatedSubAF) {
            // Sous-AF répété
            $input = new RepeatedSubAFInput($inputSet, $component);
            foreach ($data['elements'] as $ref => $elements) {
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
                        $this->doParseForm(
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
                        $this->doParseForm(
                            $elements,
                            $subInputSet,
                            $component->getCalledAF()
                        );
                        $input->addSubSet($subInputSet);
                    }
                }
            }
        } else {
            throw new \InvalidArgumentException("Unknown component " . get_class($component));
        }

        return $input;
    }

    /**
     * @param PrimaryInputSet $inputSet
     * @return array
     */
    public function serialize(PrimaryInputSet $inputSet = null)
    {
        if ($inputSet === null) {
            return null;
        }

        $data = [
            'completion' => $inputSet->getCompletion(),
            'status'     => $inputSet->getStatus(),
        ];

        $data += $this->serializeInputSet($inputSet);

        return $data;
    }

    private function serializeInputSet(InputSet $inputSet)
    {
        $data = [
            'inputs' => [],
        ];

        $locale = Core_Locale::loadDefault();

        foreach ($inputSet->getInputs() as $input) {
            $arr = [
                'componentRef' => $input->getRefComponent(),
            ];

            switch (true) {
                case $input instanceof NotRepeatedSubAFInput:
                    /** @var NotRepeatedSubAFInput $input */
                    $arr['type'] = 'subaf-single';
                    $arr['calledAF'] = $this->serializeGroup($input->getCalledAF()->getRootGroup());
                    break;
                case $input instanceof RepeatedSubAFInput:
                    /** @var RepeatedSubAFInput $input */
                    $arr['type'] = 'subaf-multi';
                    $arr['calledAF'] = $this->serializeGroup($input->getCalledAF()->getRootGroup());
                    break;
                case $input instanceof NumericFieldInput:
                    /** @var NumericFieldInput $input */
                    $value = $input->getValue();
                    $arr['value'] = [
                        'unit'         => $value->getUnit()->getRef(),
                        'digitalValue' => $locale->formatNumberForInput($value->getDigitalValue()),
                        'uncertainty'  => $locale->formatNumberForInput($value->getRelativeUncertainty()),
                    ];
                    break;
                case $input instanceof TextFieldInput:
                    /** @var TextFieldInput $input */
                    $arr['value'] = $input->getValue();
                    break;
                case $input instanceof CheckboxInput:
                    /** @var CheckboxInput $input */
                    $arr['value'] = $input->getValue();
                    break;
                case $input instanceof SelectSingleInput:
                    /** @var SelectSingleInput $input */
                    $arr['value'] = $input->getValue();
                    break;
                case $input instanceof SelectMultiInput:
                    /** @var SelectMultiInput $input */
                    $arr['value'] = $input->getValue();
                    break;
            }
            $data['inputs'][] = $arr;
        }

        return $data;
    }
}
