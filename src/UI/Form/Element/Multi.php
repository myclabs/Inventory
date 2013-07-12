<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * Interface an element within which options can be added.
 *
 * @author MyC.Sense
 * @package UI
 * @subpackage Form
 * @see UI_Form_Element_Option
 */
interface UI_Form_Element_Multi
{
    /**
     * Add an option to a Multi Element.
     *
     * @param UI_Form_Element_Option $option
     *
     * @throws Core_Exception if the option is unvalid
     */
    public function addOption(UI_Form_Element_Option $option);

    /**
     * Add a null option to a Multi Element.
     *
     * @param string $label
     * @param mixed $value
     *
     * @throws Core_Exception_InvalidArgument if this option is already added or if $label is not valid.
     */
    public function addNullOption($label, $value=null);

    /**
     * Get All option for the Element.
     *
     * @return UI_Form_Element_Option[]
     */
    public function getOptions();

    /**
     * Set a default option of a Multi Element.
     *
     * @param mixed $value
     * @see Zend_Form_Element::setValue()
     *
     * @throws Core_Exception if the value is not valid or if the option to set  is unactivated.
     * */
    public function setValue($value);

}