<?php

/**
 * Remplissage de la base de données avec des données de test
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
        $category_contenant_sous_categorie = $this->createCategory('Catégorie contenant une sous-catégorie');
        $category_sous_categorie = $this->createCategory('Sous-catégorie', $category_contenant_sous_categorie);
        $category_contenant_formulaire = $this->createCategory('Catégorie contenant un formulaire');
        $category_vide = $this->createCategory('Catégorie vide');

        // Création des af.
        // Params : Category, ref, label
        $aF_combustion_combustible_unite_masse = $this->createAF($category_contenant_formulaire, 'combustion_combustible_unite_masse', 'Combustion de combustible, mesuré en unité de masse');

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
        $group1 = $this->createGroup($aF_combustion_combustible_unite_masse, $aF_combustion_combustible_unite_masse->getRootGroup(), 'refg1', 'Label Group 1');
        $numericInput = $this->createNumericInput($aF_combustion_combustible_unite_masse, $group1, 'refn1', 'Label Numeric 1', 'm', 25, 10, true);
        $selectInputList = $this->createSelectInputList($aF_combustion_combustible_unite_masse, $group1, 'refs1', 'Label Select 1', ['o1' => 'Option 1', 'o2' => 'Option 2']);
        $booleanInput = $this->createBooleanInput($aF_combustion_combustible_unite_masse, $group1, 'refb1', 'Label Select 1', true);

        // Création des Algos.
        // Param : AF
        //  + createAlgoNumericExpression : ref, label, expression, refUnit
        //  + createAlgoNumericParameter : ref, label, refFamily
        //  + createAlgoNumericExpression : ref, label, value, uncertainty, refUnit
        //  + createAlgoNumericInput : Component input, refContext, refIndicator
        //  + createAlgoSelectTextkeyExpression : ref, expression
        //  + createAlgoConditionElementary : Component input, ref, expression
        //  + createAlgoConditionExpression : ref, expression
        // OptionalParams : -
        $aF_combustion_combustible_unite_masse->getMainAlgo()->setExpression('a:b;');
        $this->createAlgoNumericConstant($aF_combustion_combustible_unite_masse, 'refa1', 'Label 1', 10, 5, 'm');
        $this->createAlgoNumericInput($aF_combustion_combustible_unite_masse, $numericInput, 'general', 'ges');
//        $this->createAlgoSelectTextkeyExpression($aF_combustion_combustible_unite_masse, 'refa2', 'expression');
//        $this->createAlgoConditionElementary($aF_combustion_combustible_unite_masse, $booleanInput, 'refa3', 'expression');
//        $this->createAlgoConditionExpression($aF_combustion_combustible_unite_masse, 'refa4', 'expression');


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
        $aF->addComponent($component);
        $aF->getRootGroup()->addSubComponent($component);
        return $component;
    }

    /**
     * @param AF_Model_AF $aF
     * @param string $ref
     * @param string $label
     * @param string $expression
     * @param string $refUnit
     */
    protected function createAlgoNumericExpression(AF_Model_AF $aF, $ref, $label, $expression, $refUnit)
    {
        $numericExpression = new Algo_Model_Numeric_Expression();
        $numericExpression->setExpression($expression);
        $numericExpression->setUnit(new Unit_API($refUnit));
        $this->createAlgoNumeric($aF, $numericExpression, $ref, $label);
    }

    /**
     * @param AF_Model_AF $aF
     * @param string $ref
     * @param string $label
     * @param string $refFamily
     */
    protected function createAlgoNumericParameter(AF_Model_AF $aF, $ref, $label, $refFamily)
    {
        $numericParameter = new Algo_Model_Numeric_Parameter();
        $numericParameter->setFamily(Techno_Model_Family::loadByRef($refFamily));
        $this->createAlgoNumeric($aF, $numericParameter, $ref, $label);
    }

    /**
     * @param AF_Model_AF $aF
     * @param string $ref
     * @param string $label
     * @param int $value
     * @param int $uncertainty
     * @param string $refUnit
     */
    protected function createAlgoNumericConstant(AF_Model_AF $aF, $ref, $label, $value, $uncertainty, $refUnit)
    {
        $numericExpression = new Algo_Model_Numeric_Constant();
        $unitValue = new Calc_UnitValue();
        $unitValue->value->digitalValue = $value;
        $unitValue->value->relativeUncertainty = $uncertainty;
        $unitValue->unit = new Unit_API($refUnit);
        $numericExpression->setUnitValue($unitValue);
        $this->createAlgoNumeric($aF, $numericExpression, $ref, $label);
    }

    /**
     * @param AF_Model_AF $aF
     * @param Algo_Model_Numeric $numeric
     * @param string $ref
     * @param string $label
     */
    protected function createAlgoNumeric(AF_Model_AF $aF, Algo_Model_Numeric $numeric, $ref, $label)
    {
        $numeric->setRef($ref);
        $numeric->setLabel($label);
        $numeric->save();
        $aF->addAlgo($numeric);
    }

    /**
     * @param AF_Model_AF $aF
     * @param AF_Model_Component $input
     * @param string $refContext
     * @param string $refIndicator
     */
    protected function createAlgoNumericInput(AF_Model_AF $aF, AF_Model_Component $input, $refContext, $refIndicator)
    {
        /* @var Algo_Model_Numeric_Input $numericInput */
        $numericInput = $aF->getAlgoByRef($input->getRef());
        $numericInput->setContextIndicator(Classif_Model_ContextIndicator::loadByRef($refContext, $refIndicator));
    }

    /**
     * @param AF_Model_AF $aF
     * @param string $ref
     * @param string $expression
     */
    protected function createAlgoSelectTextkeyExpression(AF_Model_AF $aF, $ref, $expression)
    {
        $selectTextkeyExpression = new Algo_Model_Selection_TextKey_Expression();
        $selectTextkeyExpression->setRef($ref);
        $selectTextkeyExpression->setExpression($expression);
        $selectTextkeyExpression->save();
        $aF->addAlgo($selectTextkeyExpression);
    }

    /**
     * @param AF_Model_AF $aF
     * @param string $ref
     * @param string $expression
     */
    protected function createAlgoConditionExpression(AF_Model_AF $aF, $ref, $expression)
    {
        $conditionExpression = new Algo_Model_Condition_Expression();
        $conditionExpression->setRef($ref);
        $conditionExpression->setExpression($expression);
        $conditionExpression->save();
        $aF->addAlgo($conditionExpression);
    }

    protected function createAlgoConditionElementary(AF_Model_AF $aF, AF_Model_Component $component, $ref, $expression)
    {
        switch (get_class($component)) {
            case 'AF_Model_Component_Numeric':
                $conditionElementary = new Algo_Model_Condition_Elementary_Numeric();
                break;
            case 'AF_Model_Component_Checkbox':
                $conditionElementary = new Algo_Model_Condition_Elementary_Boolean();
                break;
            case 'AF_Model_Component_Select_Single':
                $conditionElementary = new Algo_Model_Condition_Elementary_Select_Single();
                break;
            case 'AF_Model_Component_Select_Multi':
                $conditionElementary = new Algo_Model_Condition_Elementary_Select_Multi();
                break;
            default:
                throw new Core_Exception("Unhandled field type");
        }
        $conditionElementary->setRef($ref);
        $conditionElementary->setInputRef($component->getRef());
        $conditionElementary->save();
        $aF->addAlgo($conditionElementary);
    }

}
