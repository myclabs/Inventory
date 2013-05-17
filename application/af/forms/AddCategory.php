<?php
/**
 * Formulaire d'ajout d'un AF
 * @author  matthieu.napoli
 * @package AF
 */

/**
 * @package AF
 */
class AF_Form_AddCategory extends UI_Form
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
                                         'controller' => 'tree_af-tree',
                                         'module'     => 'af'
                                         ]));

        // Ajax
        $this->setAjax(true);

        $label = new UI_Form_Element_Text('label');
        $label->setLabel(__('UI', 'name', 'label'));
        $this->addElement($label);
    }

}
