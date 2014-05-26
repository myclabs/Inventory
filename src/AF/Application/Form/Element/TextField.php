<?php

namespace AF\Application\Form\Element;

use Core_Exception_InvalidArgument;
use Zend_Form_Element_Text;

/**
 * Generate an input text.
 *
 * @author valentin.claras
 */
class TextField extends Zend_Form_Element_Text implements ZendFormElement
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

        $this->setAttrib('class', 'form-control');
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
