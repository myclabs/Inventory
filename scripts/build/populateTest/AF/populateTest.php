<?php
/**
 * @package AF
 */


/**
 * Remplissage de la base de données avec des données de test
 * @package AF
 */
class AF_PopulateTest extends Core_Script_Action
{

    /**
     * {@inheritdoc}
     */
    public function runEnvironment($environment)
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        /** @var $entityManager \Doctrine\ORM\EntityManager */
        $entityManager = $entityManagers['default'];


        // Création des catégories.
        // Params : ref
        // OptionalParams : Category parent=null
        $category1 = $this->createCategory('Label 1');
        $category2 = $this->createCategory('Label 2', $category1);

        // Création des af.
        // Params : Category, ref, label
        $aF1 = $this->createAF($category1, 'ref1', 'Label 1');

        // Création des composants.
        // Params : AF, Group, ref, label
        //  + createGroup : -
        //  + createSubAF(Repeated) : AF calledAF
        //  + createNumericInput : refUnit
        //  + createSelectInput List|Radio|Multi|Boxes : [refOtion => labelOption]
        //  + createBooleanInput : -
        // OptionalParams :
        //  + createGroup : foldaway=true
        //  + createSubAF : foldaway=true
        //  + createSubAFRepeated : foldaway=true, minimumRepetition=0, freeLabel=false
        //  + createNumericInput : defaultValue=null, defaultUncertainty=null, defaultReminder=true, required=true, enabled=true
        //  + createSelectInput List|Radio|Multi|Boxes : required=true, enabled=true
        //  + createBooleanInput : defaultValue=true
        //  help=null, visible=true
        $group1 = $this->createGroup($aF1, $aF1->getRootGroup(), 'refg1', 'Label Group 1');
        $numericInput = $this->createNumericInput($aF1, $group1, 'refn1', 'Label Numeric 1', 'm', 25, 10, true);
        $selectInputList = $this->createSelectInputList($aF1, $group1, 'refs1', 'Label Select 1', ['o1' => 'Option 1', 'o2' => 'Option 2']);
        $booleanInput = $this->createBooleanInput($aF1, $group1, 'refb1', 'Label Select 1', true);


        $entityManager->flush();

        echo "\t\tAF created".PHP_EOL;
    }

    /**
     * @param string $label
     * @param AF_Model_Category $parent
     * @return AF_Model_Category
     */
    protected function createCategory($label, AF_Model_Category $parent=null)
    {
        $category = new AF_Model_Category();
        $category->setLabel($label);
        if ($parent !== null) {
            $category->setParentCategory($parent);
        }
        $category->save();
        return $category;
    }

    /**
     * @param AF_Model_Category $category
     * @param $ref
     * @param $label
     * @return AF_Model_AF
     */
    protected function createAF(AF_Model_Category $category, $ref, $label)
    {
        $aF = new AF_Model_AF($ref);
        $aF->setLabel($label);
        $aF->save();
        $category->addAF($aF);
        return $aF;
    }

    /**
     * @param AF_Model_AF $aF
     * @param AF_Model_Component_Group $parentGroup
     * @param $ref
     * @param $label
     * @param bool $foldaway
     * @param null $help
     * @param bool $visible
     * @return AF_Model_Component_Group
     */
    protected function createGroup(AF_Model_AF $aF, AF_Model_Component_Group $parentGroup, $ref, $label,
        $foldaway=true, $help=null, $visible=true)
    {
        $group = new AF_Model_Component_Group();
        $group->setFoldaway($foldaway);
        return $this->createComponent($group, $aF,$parentGroup ,$ref, $label, $help, $visible, $foldaway);
    }

    /**
     * @param AF_Model_AF $aF
     * @param AF_Model_Component_Group $parentGroup
     * @param AF_Model_AF $calledAF
     * @param $ref
     * @param $label
     * @param bool $foldaway
     * @param null $help
     * @param bool $visible
     * @return mixed
     */
    protected function createSubAF(AF_Model_AF $aF, AF_Model_Component_Group $parentGroup, $ref, $label, AF_Model_AF $calledAF,
        $foldaway=true, $help=null, $visible=true)
    {
        $subAF = new AF_Model_Component_SubAF_NotRepeated();
        $subAF->setFoldaway($foldaway);
        return $this->createComponent($subAF, $aF,$parentGroup ,$ref, $label, $help, $visible, $foldaway);
    }

    /**
     * @param AF_Model_AF $aF
     * @param AF_Model_Component_Group $parentGroup
     * @param AF_Model_AF $calledAF
     * @param $ref
     * @param $label
     * @param bool $foldaway
     * @param int $minimumRepetition
     * @param bool $freeLabel
     * @param null $help
     * @param bool $visible
     * @return mixed
     */
    protected function createSubAFRepeated(AF_Model_AF $aF, AF_Model_Component_Group $parentGroup, $ref, $label, AF_Model_AF $calledAF,
        $foldaway=true, $minimumRepetition=0, $freeLabel=false, $help=null, $visible=true)
    {
        $subAF = new AF_Model_Component_SubAF_Repeated();
        $subAF->setMinInputNumber($minimumRepetition);
        $subAF->setWithFreeLabel($freeLabel);
        $subAF->setFoldaway($foldaway);
        return $this->createComponent($subAF, $aF,$parentGroup ,$ref, $label, $help, $visible, $foldaway);
    }

    /**
     * @param AF_Model_AF $aF
     * @param AF_Model_Component_Group $parentGroup
     * @param $ref
     * @param $label
     * @param $refUnit
     * @param null $defaultValue
     * @param null $defaultUncertainty
     * @param bool $defaultReminder
     * @param bool $required
     * @param bool $enabled
     * @param null $help
     * @param bool $visible
     * @return AF_Model_Component
     */
    protected function createNumericInput(AF_Model_AF $aF, AF_Model_Component_Group $parentGroup, $ref, $label, $refUnit,
        $defaultValue=null, $defaultUncertainty=null, $defaultReminder=true, $required=true, $enabled=true, $help=null, $visible=true)
    {
        $numericInput = new AF_Model_Component_Numeric();
        $numericInput->setUnit(new Unit_API($refUnit));
        $numericInput->setRequired($required);
        $numericInput->setEnabled($enabled);
        if ($defaultValue !== null) {
            $calcValue = new Calc_Value();
            $calcValue->digitalValue = $defaultValue;
            $calcValue->relativeUncertainty = $defaultUncertainty;
            $numericInput->setDefaultValue($calcValue);
            $numericInput->setDefaultValueReminder($defaultReminder);
        }
        return $this->createComponent($numericInput, $aF, $parentGroup ,$ref, $label, $help, $visible);
    }

    /**
     * @param AF_Model_AF $aF
     * @param AF_Model_Component_Group $parentGroup
     * @param string $ref
     * @param string $label
     * @param array $options
     * @param bool $required
     * @param bool $enabled
     * @param string $help
     * @param bool $visible
     * @return AF_Model_Component
     */
    protected function createSelectInputList(AF_Model_AF $aF, AF_Model_Component_Group $parentGroup, $ref, $label, array $options,
        $required=true, $enabled=true, $help=null, $visible=true)
    {
        $selectInput = new AF_Model_Component_Select_Single();
        $selectInput->setType(AF_Model_Component_Select_Single::TYPE_LIST);
        return $this->createSelectInput($selectInput, $aF, $parentGroup ,$ref, $label, $options, $required, $enabled, $help, $visible);
    }

    /**
     * @param AF_Model_AF $aF
     * @param AF_Model_Component_Group $parentGroup
     * @param string $ref
     * @param string $label
     * @param array $options
     * @param bool $required
     * @param bool $enabled
     * @param string $help
     * @param bool $visible
     * @return AF_Model_Component
     */
    protected function createSelectInputRadio(AF_Model_AF $aF, AF_Model_Component_Group $parentGroup, $ref, $label, array $options,
        $required=true, $enabled=true, $help=null, $visible=true)
    {
        $selectInput = new AF_Model_Component_Select_Single();
        $selectInput->setType(AF_Model_Component_Select_Single::TYPE_RADIO);
        return $this->createSelectInput($selectInput, $aF, $parentGroup ,$ref, $label, $options, $required, $enabled, $help, $visible);
    }

    /**
     * @param AF_Model_AF $aF
     * @param AF_Model_Component_Group $parentGroup
     * @param string $ref
     * @param string $label
     * @param array $options
     * @param bool $required
     * @param bool $enabled
     * @param string $help
     * @param bool $visible
     * @return AF_Model_Component
     */
    protected function createSelectInputMulti(AF_Model_AF $aF, AF_Model_Component_Group $parentGroup, $ref, $label, array $options,
        $required=true, $enabled=true, $help=null, $visible=true)
    {
        $selectInput = new AF_Model_Component_Select_Multi();
        $selectInput->setType(AF_Model_Component_Select_Multi::TYPE_MULTISELECT);
        return $this->createSelectInput($selectInput, $aF, $parentGroup ,$ref, $label, $options, $required, $enabled, $help, $visible);
    }

    /**
     * @param AF_Model_AF $aF
     * @param AF_Model_Component_Group $parentGroup
     * @param string $ref
     * @param string $label
     * @param array $options
     * @param bool $required
     * @param bool $enabled
     * @param string $help
     * @param bool $visible
     * @return AF_Model_Component
     */
    protected function createSelectInputBoxes(AF_Model_AF $aF, AF_Model_Component_Group $parentGroup, $ref, $label, array $options,
        $required=true, $enabled=true, $help=null, $visible=true)
    {
        $selectInput = new AF_Model_Component_Select_Multi();
        $selectInput->setType(AF_Model_Component_Select_Multi::TYPE_MULTICHECKBOX);
        return $this->createSelectInput($selectInput, $aF, $parentGroup ,$ref, $label, $options, $required, $enabled, $help, $visible);
    }

    /**
     * @param AF_Model_Component_Select $selectInput
     * @param AF_Model_AF $aF
     * @param AF_Model_Component_Group $parentGroup
     * @param string $ref
     * @param string $label
     * @param array $options
     * @param bool $required
     * @param bool $enabled
     * @param string $help
     * @param bool $visible
     * @return AF_Model_Component
     */
    protected function createSelectInput(AF_Model_Component_Select $selectInput, AF_Model_AF $aF, AF_Model_Component_Group $parentGroup,
        $ref, $label, array $options, $required=true, $enabled=true, $help=null, $visible=true)
    {
        $selectInput->setRequired($required);
        $selectInput->setEnabled($enabled);
        foreach ($options as $refOption => $labelOption) {
            $option = new AF_Model_Component_Select_Option();
            $option->setSelect($selectInput);
            $option->setRef($refOption);
            $option->setLabel($labelOption);
        }
        return $this->createComponent($selectInput, $aF, $parentGroup ,$ref, $label, $help, $visible);
    }

    /**
     * @param AF_Model_AF $aF
     * @param AF_Model_Component_Group $parentGroup
     * @param string $ref
     * @param string $label
     * @param bool $defaultValue
     * @param bool $enabled
     * @param string $help
     * @param bool $visible
     * @return AF_Model_Component
     */
    protected function createBooleanInput(AF_Model_AF $aF, AF_Model_Component_Group $parentGroup, $ref, $label,
        $defaultValue=true, $enabled=true, $help=null, $visible=true)
    {
        $boolean = new AF_Model_Component_Checkbox();
        $boolean->setDefaultValue($defaultValue);
        $boolean->setEnabled($enabled);
        return $this->createComponent($boolean, $aF, $parentGroup ,$ref, $label, $help, $visible);
    }

    /**
     * @param AF_Model_Component $component
     * @param AF_Model_AF $aF
     * @param AF_Model_Component_Group $parentGroup
     * @param $ref
     * @param $label
     * @param null $help
     * @param bool $visible
     * @return AF_Model_Component
     */
    protected function createComponent(AF_Model_Component $component, AF_Model_AF $aF, AF_Model_Component_Group $parentGroup, $ref, $label,
        $help=null, $visible=true)
    {
        $component->setAf($aF);
        $component->setGroup($parentGroup);
        $component->setRef($ref);
        $component->setLabel($label);
        $component->setHelp($help);
        $component->setVisible($visible);
        $component->save();
        return $component;
    }

}
