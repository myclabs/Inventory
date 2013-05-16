<?php
/**
 * Formulaire d'édition d'une famille
 * @author matthieu.napoli
 * @package Techno
 */

/**
 * @package Techno
 */
class Techno_Form_EditFamily extends UI_Form
{

    /**
     * @param string $ref
     * @param Techno_Model_Family $family
     */
    public function __construct($ref, Techno_Model_Family $family)
    {
        parent::__construct($ref);

        $urlHelper = new Zend_View_Helper_Url();
        $this->setAction($urlHelper->url([
                                         'action'     => 'submit',
                                         'controller' => 'form_edit-family',
                                         'module'     => 'techno'
                                         ]));
        $this->setAjax(true);

        // Type
        $typeSelection = new UI_Form_Element_Select('type');
        $typeSelection->setLabel('Type');
        $typeSelection->getElement()->disabled = true;
        $typeSelection->addOption(new UI_Form_Element_Option('coeff', 'coeff', __('Techno', 'name', 'coefficient')));
        $typeSelection->addOption(new UI_Form_Element_Option('process', 'process', __('Techno', 'name', 'emissionFactor')));
        if ($family instanceof Techno_Model_Family_Process) {
            $typeSelection->setValue('process');
        } else {
            $typeSelection->setValue('coeff');
        }
        $this->addElement($typeSelection);

        // Libellé
        $label = new UI_Form_Element_Text('label');
        $label->setLabel(__('UI', 'name', 'label'));
        $label->setValue($family->getLabel());
        $this->addElement($label);

        // Identifiant
        $ref = new UI_Form_Element_Text('ref');
        $ref->setLabel(__('UI', 'name', 'identifier'));
        $ref->setValue($family->getRef());
        $this->addElement($ref);

        // Unité
        $unit = new UI_Form_Element_Text('unit');
        $unit->setLabel(__('Unit', 'name', 'unit'));
        $unit->setValue($family->getUnit()->getRef());
        $this->addElement($unit);

        // Id de la famille
        $idFamilyField = new UI_Form_Element_Hidden('id');
        $idFamilyField->setValue($family->getId());
        $this->addElement($idFamilyField);

        $this->addSubmitButton();
    }

}
