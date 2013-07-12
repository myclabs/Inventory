<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * Generate a multicheckbox input.
 *
 * @package UI
 * @subpackage Form
 */
class UI_Form_Element_MultiCheckbox extends Zend_Form_Element_MultiCheckbox
    implements UI_Form_Element_Multi, UI_Form_ZendElement
{
    /**
     * Reference to a UI_Form_Element, to access to its methods.
     *
     * @var UI_Form_Element
     */
    protected $_element;

    /**
     * List of options
     *
     * @var UI_Form_Element_Option[]
     */
    protected $_options = array();


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
     * @param UI_Form_Element_Option $option
     * @see Core/Form/Element/UI_Form_Element_Multi::addOption()
     */
    public function addOption(UI_Form_Element_Option $option)
    {
        $this->_options[$option->ref] = $option;
        $this->addMultiOption($option->value, $option->label);
    }

    /**
     * @param string $label
     * @param unknow $value
     * @see Core/Form/Element/UI_Form_Element_Multi::addNullOption()
     */
    public function addNullOption($label, $value = null)
    {
        $nullOption = new UI_Form_Element_Option('nullOption', $value, $label);
        $this->_options[$nullOption->ref] = $nullOption;
        $this->addMultiOption($nullOption->value, $nullOption->label);
    }

    /**
     * Get All option for the Element.
     *
     * @return UI_Form_Element_Option[]
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * @param Zend_View_Interface $view
     * @see Zend/Form/Zend_Form_Element::render()
     */
    public function render(Zend_View_Interface $view = null)
    {
        $content = '';

        $globalWrapperOption = array(
                'tag' => 'div',
                'id'  => $this->getId()
        );

        foreach ($this->_options as $option) {
            $idOption = $this->getId().'_'.$option->value;
            $labelOptions = array(
                'tag'   => 'label',
                'for'   => $idOption,
                'class' => 'checkbox',
            );
            if ($option->hidden) {
                $liOptions['class'] = 'hide';
            }

            $inputSelected = '';
            $getValue = $this->getValue();
            if (isset($getValue)) {
                if (!is_array($getValue)) {
                    if ($getValue === $option->value) {
                        $inputSelected = ' checked="checked"';
                    }
                } else {
                    foreach ($getValue as $value) {
                        if ($value === $option->value) {
                            $inputSelected = ' checked="checked"';
                        }
                    }
                }
            }

            $inputDisabled = '';
            if (($this->getElement()->disabled) || ($option->disabled)) {
                $inputDisabled = ' disabled="disabled"';
            }

            $input = '<input';
            $input .= ' id="'.$idOption.'"';
            $input .= ' type="checkbox"';
            $input .= ' name="'.$this->getName().'"';
            $input .= ' value="'.$option->value.'" ';
            $input .= $inputSelected . $inputDisabled;
            $input .= '/>';

            $htmlTagDecorator = new Zend_Form_Decorator_HtmlTag();
            $htmlTagDecorator->setOptions($labelOptions);
            $content .= $htmlTagDecorator->render($input.$option->label);
        }

        $htmlTagDecorator = new Zend_Form_Decorator_HtmlTag();
        $htmlTagDecorator->setOptions($globalWrapperOption);
        $content = $htmlTagDecorator->render($content);

        foreach ($this->getDecorators() as $decorator) {
            $decorator->setElement($this);
            $content = $decorator->render($content);
        }

        return $content;
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