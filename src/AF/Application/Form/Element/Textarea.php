<?php

namespace AF\Application\Form\Element;

use Core_Exception_InvalidArgument;
use Zend_Form_Element_Textarea;

/**
 * Generate an textarea input.
 *
 * @author valentin.claras
 */
class Textarea extends Zend_Form_Element_Textarea implements ZendFormElement
{
    /**
     * @var FormElement
     */
    protected $_element;

    /**
     * Enable or disable rich text editor.
     *
     * @var bool
     */
    protected $_withMarkItUp = false;


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
        $this->setAttrib('rows', 4);
        $this->setAttrib('cols', 55);

        $this->setAttrib('class', 'form-control');
    }

    /**
     * Set if MarkItUp will be or not over the textarea.
     *
     * @param bool $flag
     */
    public function setWithMarkItUp($flag)
    {
        $this->_withMarkItUp = $flag;
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
        if ($this->_withMarkItUp === true) {
            return '$("#' . $this->getId() . '").markItUp(mySettings);';
        }
        return '';
    }
}
