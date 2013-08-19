<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * Generate an input numeric.
 *
 * @package UI
 * @subpackage Form
 */
class UI_Form_Element_Numeric extends Zend_Form_Element_Xhtml implements UI_Form_ZendElement
{
    /**
     * Default form view helper to use for rendering
     * @var string
     */
    public $helper = 'formNumeric';

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
     * @param bool $isTypeNumber
     *
     * @throws Core_Exception_InvalidArgument if $name is not valid.
     */
    public function __construct($name, $isTypeNumber=false)
    {
        if (!(is_string($name))) {
            throw new Core_Exception_InvalidArgument('Name is required for the Element');
        }
        parent::__construct($name);

        $this->_element = new UI_Form_Element($this);

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