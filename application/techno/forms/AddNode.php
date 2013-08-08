<?php
/**
 * Formulaire d'édition d'un noeud
 * @author ronan.gorain
 * @author matthieu.napoli
 * @package Techno
 */

/**
 * @package Techno
 */
class Techno_Form_AddNode extends UI_Form
{

    /**
     * constructeur du formulaire
     * @param string $ref
     */
    public function __construct($ref)
    {
        parent::__construct($ref);

        $urlHelper = new Zend_View_Helper_Url();
        $this->setAction($urlHelper->url([
                                         'action'     => 'addnode',
                                         'controller' => 'tree_family-tree',
                                         'module'     => 'techno'
                                         ]));

        // Ajax
        $this->setAjax(true, 'onAddCategorySuccess');

        $label = new UI_Form_Element_Text('label');
        $label->setLabel(__('UI', 'name', 'label'));
        $this->addElement($label);
    }

}
