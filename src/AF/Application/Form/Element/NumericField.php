<?php

namespace AF\Application\Form\Element;

use Core_Exception_InvalidArgument;
use Zend_Form_Element_Xhtml;

/**
 * Generate an input numeric.
 *
 * @author valentin.claras
 */
class NumericField extends Zend_Form_Element_Xhtml implements ZendFormElement
{
    /**
     * Default form view helper to use for rendering
     * @var string
     */
    public $helper = 'formNumeric';

    /**
     * @var FormElement
     */
    protected $_element;


    /**
     * @param string $name
     * @param bool   $isTypeNumber
     *
     * @throws Core_Exception_InvalidArgument if $name is not valid.
     */
    public function __construct($name, $isTypeNumber = false)
    {
        if (!(is_string($name))) {
            throw new Core_Exception_InvalidArgument('Name is required for the Element');
        }
        parent::__construct($name);

        $this->_element = new FormElement($this);

        $this->setAttrib('class', 'form-control');

        if ($isTypeNumber) {
            $this->setAttrib('type', 'number');
            $this->setAttrib('step', 'any');
        } else {
            $this->setAttrib('type', 'text');
            $this->setAttrib('pattern', '-?[0-9]*[.,]?[0-9]*');
        }
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
