<?php

namespace AF\Architecture\Service;

use AF\Domain\AF;
use AF\Domain\Component\Checkbox;
use AF\Domain\Component\Group;
use AF\Domain\Component\NumericField;
use AF\Domain\Component\Select\SelectMulti;
use AF\Domain\Component\Select\SelectSingle;
use AF\Domain\Component\TextField;
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

    public function serialize(AF $af)
    {
        $data = [
            'label' => $this->translator->get($af->getLabel()),
        ];

        $data += $this->serializeGroup($af->getRootGroup());

        return json_encode($data, JSON_PRETTY_PRINT);
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
            ];

            switch (true) {
                case $component instanceof Group:
                    $arr['type'] = 'group';
                    $arr += $this->serializeGroup($component);
                    break;
                case $component instanceof NumericField:
                    /** @var NumericField $component */
                    $arr['type'] = 'numeric';
                    $arr['unit'] = $component->getUnit()->getRef();
                    $arr['required'] = $component->getRequired();
                    break;
                case $component instanceof TextField:
                    /** @var TextField $component */
                    if ($component->getType() === TextField::TYPE_SHORT) {
                        $arr['type'] = 'text';
                    } else {
                        $arr['type'] = 'textarea';
                    }
                    $arr['required'] = $component->getRequired();
                    break;
                case $component instanceof Checkbox:
                    $arr['type'] = 'checkbox';
                    break;
                case $component instanceof SelectSingle:
                    /** @var SelectSingle $component */
                    if ($component->getType() === SelectSingle::TYPE_LIST) {
                        $arr['type'] = 'select';
                    } else {
                        $arr['type'] = 'radio';
                    }
                    $arr['required'] = $component->getRequired();
                    $arr['options'] = [];
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
}
