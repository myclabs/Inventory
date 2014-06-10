<?php

namespace AF\Architecture\Service;

use AF\Domain\Action\Action;
use AF\Domain\Action\SetState;
use AF\Domain\Action\SetValue;
use AF\Domain\AF;
use AF\Domain\Component\Checkbox;
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
use Core_Tools;
use Mnapoli\Translated\Translator;

/**
 * Service permettant de serializer un AF.
 *
 * @author matthieu.napoli
 */
class AFSerializer
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
     * @param AF $af
     * @return array
     */
    public function serialize(AF $af)
    {
        $data = [
            'id'    => $af->getId(),
            'label' => $this->translator->get($af->getLabel()),
        ];

        $data += $this->serializeGroup($af->getRootGroup());

        return $data;
    }

    private function serializeGroup(Group $group)
    {
        $data = [
            'components' => [],
        ];

        foreach ($group->getSubComponents() as $component) {
            $arr = [
                'ref'     => $component->getRef(),
                'label'   => $this->translator->get($component->getLabel()),
                'visible' => $component->isVisible(),
                'help'    => Core_Tools::textile($this->translator->get($component->getHelp())),
            ];

            foreach ($component->getActions() as $action) {
                $arr['actions'][] = $this->serializeAction($action);
            }

            switch (true) {
                case $component instanceof Group:
                    /** @var Group $component */
                    $arr['type'] = 'group';
                    $arr += $this->serializeGroup($component);
                    break;
                case $component instanceof NotRepeatedSubAF:
                    /** @var NotRepeatedSubAF $component */
                    $arr['type'] = 'subaf-single';
                    $arr['calledAF'] = $this->serializeGroup($component->getCalledAF()->getRootGroup());
                    break;
                case $component instanceof RepeatedSubAF:
                    /** @var RepeatedSubAF $component */
                    $arr['type'] = 'subaf-multi';
                    $arr['calledAF'] = $this->serializeGroup($component->getCalledAF()->getRootGroup());
                    switch ($component->getMinInputNumber()) {
                        default:
                        case RepeatedSubAF::MININPUTNUMBER_0:
                            $arr['init'] = 'none';
                            break;
                        case RepeatedSubAF::MININPUTNUMBER_1_DELETABLE:
                            $arr['init'] = 'one_deletable';
                            break;
                        case RepeatedSubAF::MININPUTNUMBER_1_NOT_DELETABLE:
                            $arr['init'] = 'one_not_deletable';
                            break;
                    }
                    break;
                case $component instanceof NumericField:
                    /** @var NumericField $component */
                    $unit = $component->getUnit();
                    $arr['type'] = 'numeric';
                    $arr['enabled'] = $component->isEnabled();
                    $arr['unit'] = [
                        'ref'    => $unit->getRef(),
                        'symbol' => $this->translator->get($unit->getSymbol()),
                    ];
                    if ($component->hasUnitSelection()) {
                        $arr['unitChoices'] = [ $arr['unit'] ];
                        foreach ($unit->getCompatibleUnits() as $compatibleUnit) {
                            $arr['unitChoices'][] = [
                                'ref'    => $compatibleUnit->getRef(),
                                'symbol' => $this->translator->get($compatibleUnit->getSymbol()),
                            ];
                        }
                    }
                    $arr['required'] = $component->getRequired();
                    $arr['withUncertainty'] = $component->getWithUncertainty();
                    $arr['defaultValue']['digitalValue'] = $component->getDefaultValue()->getDigitalValue();
                    $arr['defaultValue']['uncertainty'] = $component->getDefaultValue()->getRelativeUncertainty();
                    $arr['defaultValue']['reminder'] = $component->getDefaultValueReminder();
                    break;
                case $component instanceof TextField:
                    /** @var TextField $component */
                    if ($component->getType() === TextField::TYPE_SHORT) {
                        $arr['type'] = 'text';
                    } else {
                        $arr['type'] = 'textarea';
                    }
                    $arr['enabled'] = $component->isEnabled();
                    $arr['required'] = $component->getRequired();
                    break;
                case $component instanceof Checkbox:
                    /** @var Checkbox $component */
                    $arr['type'] = 'checkbox';
                    $arr['enabled'] = $component->isEnabled();
                    break;
                case $component instanceof SelectSingle:
                    /** @var SelectSingle $component */
                    if ($component->getType() === SelectSingle::TYPE_LIST) {
                        $arr['type'] = 'select';
                        $defaultOptions = [
                            [
                                'ref'   => 'null',
                                'label' => '',
                            ],
                        ];
                    } else {
                        $arr['type'] = 'radio';
                        $defaultOptions = [];
                    }
                    $arr['enabled'] = $component->isEnabled();
                    $arr['required'] = $component->getRequired();
                    $arr['options'] = $defaultOptions;
                    foreach ($component->getOptions() as $option) {
                        $arr['options'][] = [
                            'ref'   => $option->getRef(),
                            'label' => $this->translator->get($option->getLabel()),
                        ];
                    }
                    break;
                case $component instanceof SelectMulti:
                    /** @var SelectMulti $component */
                    $arr['type'] = 'select-multiple';
                    $arr['enabled'] = $component->isEnabled();
                    $arr['required'] = $component->getRequired();
                    $arr['options'] = [];
                    foreach ($component->getOptions() as $option) {
                        $arr['options'][] = [
                            'ref'   => $option->getRef(),
                            'label' => $this->translator->get($option->getLabel()),
                        ];
                    }
                    break;
                default:
                    $arr['type'] = null;
            }
            $data['components'][] = $arr;
        }

        return $data;
    }

    private function serializeAction(Action $action)
    {
        /** @var ElementaryCondition $condition */
        $condition = $action->getCondition();

        switch (true) {
            case $action instanceof SetState:
                /** @var $action SetState */
                switch ($action->getState()) {
                    case Action::TYPE_SHOW:
                        $type = 'show';
                        break;
                    case Action::TYPE_HIDE:
                        $type = 'hide';
                        break;
                    case Action::TYPE_ENABLE:
                        $type = 'enable';
                        break;
                    case Action::TYPE_DISABLE:
                        $type = 'disable';
                        break;
                    default:
                        $type = null;
                }
                break;
            case $action instanceof SetValue:
                $type = 'set-value';
                break;
            default:
                $type = null;
        }

        if (!$type) {
            return null;
        }

        return [
            'type'      => $type,
            'condition' => $condition ? $this->serializeCondition($condition) : null,
        ];
    }

    private function serializeCondition(Condition $condition)
    {
        $type = null;
        if ($condition instanceof ElementaryCondition) {
            switch ($condition->getRelation()) {
                case ElementaryCondition::RELATION_EQUAL:
                    $type = 'equal';
                    break;
                case ElementaryCondition::RELATION_NEQUAL:
                    $type = 'nequal';
                    break;
            }
        }

        $targetFieldRef = $condition->getField() ? $condition->getField()->getRef() : '';

        switch (true) {
            case $condition instanceof CheckboxCondition:
                /** @var $condition CheckboxCondition */
                return [
                    'type'            => $type,
                    'targetComponent' => $targetFieldRef,
                    'value'           => $condition->getValue(),
                ];
            case $condition instanceof SelectSingleCondition:
                /** @var $condition SelectSingleCondition */
                $optionRef = $condition->getOption() ? $condition->getOption()->getRef() : '';
                return [
                    'type'            => $type,
                    'targetComponent' => $targetFieldRef,
                    'value'           => $optionRef,
                ];
        }

        return null;
    }
}
