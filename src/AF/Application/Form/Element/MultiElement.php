<?php

namespace AF\Application\Form\Element;

use Core_Exception;
use Core_Exception_InvalidArgument;

/**
 * Interface an element within which options can be added.
 *
 * @author valentin.claras
 */
interface MultiElement
{
    /**
     * Add an option to a Multi Element.
     *
     * @param Option $option
     *
     * @throws Core_Exception if the option is unvalid
     */
    public function addOption(Option $option);

    /**
     * Add a null option to a Multi Element.
     *
     * @param string $label
     * @param mixed  $value
     *
     * @throws Core_Exception_InvalidArgument if this option is already added or if $label is not valid.
     */
    public function addNullOption($label, $value = null);

    /**
     * Get All option for the Element.
     *
     * @return Option[]
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
