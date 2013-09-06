<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * Generate a group wich contains elements
 *
 * @package UI
 * @subpackage Form
 */
class UI_Form_Element_GroupRepeated extends UI_Form_Element_Group
{
    /**
     * Ensemble des lignes de valeurs ajoutées par défaut.
     *
     * @var UI_Form_Element_Group[]
     */
    protected $_lineValues = array();

    /**
     * @see Zend/Form/Zend_Form_Element::loadDefaultDecorators()
     */
    public function loadDefaultDecorators()
    {
        $this->addPrefixPath(
            'UI_Form_Decorator',
            dirname(__FILE__).'/../Decorator/',
            Zend_Form_Element::DECORATOR
        );
        $this->clearDecorators();
        $this->addDecorator('GroupRepeated');
        $this->addDecorator('Group');
    }

    /**
     * Get the associated LineValues.
     *
     * @return UI_Form_Element_Group[]
     */
    public function getLineValues()
    {
        return $this->_lineValues;
    }

    /**
     * Add a line of values to the default render
     *
     * @param UI_Form_Element_Group $lineValue
     */
    public function addLineValue(UI_Form_Element_Group $lineValue)
    {
        $this->_lineValues[] = $lineValue;
    }

}
