<?php

namespace Techno\Application\Form;

use Zend_View_Helper_Url;
use Techno\Domain\Family\Family;
use UI_Form;
use UI_Form_Element_Text;
use UI_Form_Element_Hidden;

/**
 * Formulaire d'édition d'une famille
 * @author matthieu.napoli
 */
class EditFamilyForm extends UI_Form
{
    /**
     * @param string $ref
     * @param Family $family
     */
    public function __construct($ref, Family $family)
    {
        parent::__construct($ref);

        $urlHelper = new Zend_View_Helper_Url();
        $this->setAction($urlHelper->url([
                                         'action'     => 'submit',
                                         'controller' => 'form_edit-family',
                                         'module'     => 'techno'
                                         ]));
        $this->setAjax(true);

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
