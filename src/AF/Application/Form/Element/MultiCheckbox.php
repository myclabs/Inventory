<?php

namespace AF\Application\Form\Element;

use Core_Exception_InvalidArgument;
use AF\Application\Form\Element\Option;
use Zend_Form_Decorator_HtmlTag;
use Zend_Form_Element_MultiCheckbox;
use Zend_View_Interface;

/**
 * Generate a multicheckbox input.
 *
 * @author valentin.claras
 */
class MultiCheckbox extends Zend_Form_Element_MultiCheckbox implements MultiElement, ZendFormElement
{
    /**
     * @var FormElement
     */
    protected $_element;

    /**
     * List of options
     *
     * @var Option[]
     */
    protected $_options = array();


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

    public function addOption(Option $option)
    {
        $this->_options[$option->ref] = $option;
        $this->addMultiOption($option->value, $option->label);
    }

    public function addNullOption($label, $value = null)
    {
        $nullOption = new Option('nullOption', $value, $label);
        $this->_options[$nullOption->ref] = $nullOption;
        $this->addMultiOption($nullOption->value, $nullOption->label);
    }

    public function getOptions()
    {
        return $this->_options;
    }

    public function render(Zend_View_Interface $view = null)
    {
        $content = '';

        $globalWrapperOption = array(
            'tag' => 'div',
            'id'  => $this->getId()
        );

        foreach ($this->_options as $option) {
            $idOption = $this->getId() . '_' . $option->value;
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
            $input .= ' id="' . $idOption . '"';
            $input .= ' type="checkbox"';
            $input .= ' name="' . $this->getName() . '"';
            $input .= ' value="' . $option->value . '" ';
            $input .= $inputSelected . $inputDisabled;
            $input .= '/>';

            $htmlTagDecorator = new Zend_Form_Decorator_HtmlTag();
            $htmlTagDecorator->setOptions($labelOptions);
            $content .= $htmlTagDecorator->render($input . $option->label);
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
     * Utilisé par Form pour fournir les scripts javascripts.
     *
     * @return string
     */
    public function getScript()
    {
        return '';
    }
}
