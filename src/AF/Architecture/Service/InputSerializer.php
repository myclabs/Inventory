<?php

namespace AF\Architecture\Service;

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
use AF\Domain\Input\CheckboxInput;
use AF\Domain\Input\GroupInput;
use AF\Domain\Input\NumericFieldInput;
use AF\Domain\Input\Select\SelectMultiInput;
use AF\Domain\Input\Select\SelectSingleInput;
use AF\Domain\Input\SubAF\NotRepeatedSubAFInput;
use AF\Domain\Input\SubAF\RepeatedSubAFInput;
use AF\Domain\Input\TextFieldInput;
use AF\Domain\InputService;
use AF\Domain\InputSet\InputSet;
use AF\Domain\InputSet\PrimaryInputSet;
use AF\Domain\InputSet\SubInputSet;
use Calc_UnitValue;
use Core_Exception_InvalidArgument;
use Core_Exception_NotFound;
use Core_Locale;
use Mnapoli\Translated\Translator;
use MyCLabs\UnitAPI\Exception\IncompatibleUnitsException;
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

    /**
     * @var InputService
     */
    private $inputService;

    public function __construct(Translator $translator, InputService $inputService)
    {
        $this->translator = $translator;
        $this->inputService = $inputService;
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
        try {
            $component = Component::loadByRef($data['componentRef'], $af);
        } catch (Core_Exception_NotFound $e) {
            // Champ introuvable ignoré
            return;
        }

        if ($component instanceof NumericField) {
            // Champ numérique
            $input = new NumericFieldInput($inputSet, $component);
            $inputSet->setInputForComponent($component, $input);
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
            if (isset($data['inconsistent']) && $data['inconsistent'] == 'true') {
                $input->setInconsistentValue(true);
            } else {
                $input->setInconsistentValue(false);
            }

        } elseif ($component instanceof TextField) {
            // Champ texte
            $input = new TextFieldInput($inputSet, $component);
            $inputSet->setInputForComponent($component, $input);
            if (!empty($data['value'])) {
                $input->setValue($data['value']);
            }

        } elseif ($component instanceof Checkbox) {
            // Champ checkbox
            $input = new CheckboxInput($inputSet, $component);
            $inputSet->setInputForComponent($component, $input);
            if (isset($data['value'])) {
                if (is_string($data['value'])) {
                    $value = ($data['value'] === 'true') ? true : false;
                } else {
                    $value = (bool) $data['value'];
                }
                $input->setValue($value);
            }

        } elseif ($component instanceof SelectSingle) {
            // Champ de sélection simple
            $input = new SelectSingleInput($inputSet, $component);
            $inputSet->setInputForComponent($component, $input);
            if (! empty($data['value'])) {
                $option = $component->getOptionByRef($data['value']);
            } else {
                $option = null;
            }
            $input->setValue($option);

        } elseif ($component instanceof SelectMulti) {
            // Champ de sélection multiple
            $input = new SelectMultiInput($inputSet, $component);
            $inputSet->setInputForComponent($component, $input);
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
            $inputSet->setInputForComponent($component, $input);
            if (isset($data['value']['inputs'])) {
                foreach ($data['value']['inputs'] as $inputData) {
                    $this->unserializeInput($inputData, $input->getValue(), $component->getCalledAF());
                }
            }

        } elseif ($component instanceof RepeatedSubAF) {
            // Sous-AF répété
            $input = new RepeatedSubAFInput($inputSet, $component);
            $inputSet->setInputForComponent($component, $input);
            $subInputSets = $input->getValue();

            if (! isset($data['value']) || ! is_array($data['value'])) {
                $data['value'] = [];
            }

            foreach ($data['value'] as $repetitionNumber => $repetition) {
                if (isset($subInputSets[$repetitionNumber])) {
                    $subInputSet = $subInputSets[$repetitionNumber];
                } else {
                    $subInputSet = new SubInputSet($component->getCalledAF());
                    $input->addSubSet($subInputSet);
                }

                // Free label
                if (isset($repetition['freeLabel'])) {
                    $subInputSet->setFreeLabel($repetition['freeLabel']);
                }

                if (isset($repetition['inputs'])) {
                    foreach ($repetition['inputs'] as $inputData) {
                        $this->unserializeInput($inputData, $subInputSet, $component->getCalledAF());
                    }
                }
            }
        } elseif ($component instanceof Group) {
            return;
        } else {
            throw new \InvalidArgumentException("Unknown component " . get_class($component));
        }

        $visible = ($data['visible'] === 'true') ? true : false;
        $input->setHidden(! $visible);
    }

    /**
     * @param PrimaryInputSet $inputSet
     * @param PrimaryInputSet $previousInputSet
     * @return array
     */
    public function serialize(PrimaryInputSet $inputSet = null, PrimaryInputSet $previousInputSet = null)
    {
        if ($inputSet === null) {
            return null;
        }

        $data = [
            'completion' => $inputSet->getCompletion(),
            'status'     => $inputSet->getStatus(),
        ];

        $data += $this->serializeInputSet($inputSet, $previousInputSet);

        return $data;
    }

    private function serializeInputSet(InputSet $inputSet, InputSet $previousInputSet = null)
    {
        $data = [
            'inputs' => [],
        ];

        if ($inputSet->getId()) {
            $data['id'] = $inputSet->getId();
        }

        if ($inputSet instanceof SubInputSet) {
            $data['freeLabel'] = $inputSet->getFreeLabel();
        }

        foreach ($inputSet->getInputs() as $input) {
            if ($input instanceof GroupInput) {
                continue;
            }

            $arr = [
                'componentRef' => $input->getRefComponent(),
                'visible'      => ! $input->isHidden(),
            ];

            if ($input->getId()) {
                $arr['id'] = $input->getId();
            }

            // Saisie précédente
            $previousInput = null;
            if ($previousInputSet) {
                $previousInput = $previousInputSet->getInputByRef($input->getRefComponent());
            }

            switch (true) {
                case $input instanceof NotRepeatedSubAFInput:
                    /** @var NotRepeatedSubAFInput $input */
                    $previousValue = $previousInput ? $previousInput->getValue() : null;
                    $arr['value'] = $this->serializeInputSet($input->getValue(), $previousValue);
                    break;
                case $input instanceof RepeatedSubAFInput:
                    /** @var RepeatedSubAF $component */
                    $component = $input->getComponent();
                    $templateInputSet = new SubInputSet($component->getCalledAF());
                    $component->getCalledAF()->initializeNewInput($templateInputSet);
                    $arr['subInputSetTemplate'] = $this->serializeInputSet($templateInputSet);
                    /** @var RepeatedSubAFInput $input */
                    foreach ($input->getValue() as $inputSet) {
                        $arr['value'][] = $this->serializeInputSet($inputSet);
                    }
                    break;
                case $input instanceof NumericFieldInput:
                    /** @var NumericFieldInput $input */
                    $value = $input->getValue();
                    $arr['value'] = [
                        'unit'         => $value->getUnit()->getRef(),
                        'digitalValue' => $value->getDigitalValue(),
                        'uncertainty'  => $value->getUncertainty(),
                    ];
                    if ($previousInput instanceof NumericFieldInput) {
                        try {
                            $previousValue = $previousInput->getValue()->convertTo($value->getUnit());
                            $arr['previousValue'] = $previousValue->getDigitalValue();
                        } catch (IncompatibleUnitsException $e) {
                        }
                    }
                    $arr['inconsistent'] = $input->hasInconsistentValue();
                    break;
                case $input instanceof TextFieldInput:
                    /** @var TextFieldInput $input */
                    $arr['value'] = $input->getValue();
                    if ($previousInput instanceof TextFieldInput) {
                        $arr['previousValue'] = $previousInput->getValue();
                    }
                    break;
                case $input instanceof CheckboxInput:
                    /** @var CheckboxInput $input */
                    $arr['value'] = $input->getValue();
                    if ($previousInput instanceof CheckboxInput) {
                        $arr['previousValue'] = $previousInput->getValue();
                    }
                    break;
                case $input instanceof SelectSingleInput:
                    /** @var SelectSingleInput $input */
                    $arr['value'] = $input->getValue();
                    if ($previousInput instanceof SelectSingleInput) {
                        $arr['previousValue'] = $previousInput->getValue();
                    }
                    break;
                case $input instanceof SelectMultiInput:
                    /** @var SelectMultiInput $input */
                    $arr['value'] = $input->getValue();
                    if ($previousInput instanceof SelectMultiInput) {
                        $arr['previousValue'] = $previousInput->getValue();
                    }
                    break;
            }
            $data['inputs'][] = $arr;
        }

        return $data;
    }
}
