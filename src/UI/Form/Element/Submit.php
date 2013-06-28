<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * Generate an input submit.
 *
 * @package UI
 * @subpackage Form
 */
class UI_Form_Element_Submit extends Zend_Form_Element_Submit
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
     * @param array|string|Zend_Config $ref
     * @param string $label
     * @throws Core_Exception_InvalidArgument
     */
    public function __construct($ref, $label=null)
    {
        if (!(is_string($ref))) {
            throw new Core_Exception_InvalidArgument('Name is required for the Element');
        }
        parent::__construct($ref, $label);
        $this->_element = new UI_Form_Element($this);

        $this->setAttrib('class', 'btn btn-primary');
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
