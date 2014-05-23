<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * Generate an input text.
 *
 * @package UI
 * @subpackage Form
 */
class UI_Form_Element_Text extends Zend_Form_Element_Text implements UI_Form_ZendElement
{
    /**
     * Reference to a UI_Form_Element, to access to its methods.
     *
     * @var UI_Form_Element
     */
    protected $_element;


    /**
     * Constructor
     *
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
        $this->_element = new UI_Form_Element($this);

        $this->setAttrib('class', 'form-control');
    }

    /**
     * Get the associated Element.
     *
     * @return UI_Form_Element
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * Utilisé par UI_Form pour fournir les scripts javascripts.
     *
     * @return string
     */
    public function getScript()
    {
        return '';
    }

}
