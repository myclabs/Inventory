<?php

namespace AF\Application\Form\Element;

use Core_Exception_InvalidArgument;
use Zend_Form_Element_Checkbox;

/**
 * Generate a checkbox input.
 *
 * @author valentin.claras
 */
class Checkbox extends Zend_Form_Element_Checkbox implements ZendFormElement
{
    /**
     * @var FormElement
     */
    protected $_element;


    /**
     * @param string $name
     *
     * @throws Core_Exception_InvalidArgument if $name is not valid.
     */
    public function __construct($name)
    {
        if (!(is_string($name))) {
            throw new Core_Exception_InvalidArgument('Name is required for the Element');
        }
        parent::__construct($name);
        $this->_element = new FormElement($this);
    }

    /**
     * Get the associated Element.
     *
     * @return FormElement
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * Utilisé par Form pour fournir les scripts javascripts.
     *
     * @return string
     */
    public function getScript()
    {
        return '';
    }
}
