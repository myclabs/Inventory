<?php

namespace AF\Application\Form\Element;

use Core_Exception_InvalidArgument;
use UI_JS_AutoComplete;
use Zend_Form_Decorator_HtmlTag;
use Zend_Form_Element_Multiselect;
use Zend_View_Interface;

/**
 * Generate a multiselect input.
 *
 * @author valentin.claras
 */
class MultiSelect extends Zend_Form_Element_Multiselect implements MultiElement, ZendFormElement
{
    /**
     * @var FormElement
     */
    protected $_element;

    /**
     * List of options.
     *
     * @var Option[]
     */
    protected $_options = array();

    /**
     * Size of the multi.
     *
     * @var int
     */
    public $size = null;

    /**
     * Indique se le champs utilisera l'autocompletion.
     *
     * @var bool
     */
    public $useAutocomplete = false;


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

        $getValue = $this->getValue();
        // Size of the multi
        $size = 0;
        foreach ($this->_options as $optionElement) {
            $idOption = $this->getId() . '_' . $optionElement->value;
            $optionOptions = array(
                'tag'   => 'option',
                'id'    => $idOption,
                'value' => $optionElement->value
            );

            if (isset($getValue)) {
                if (!is_array($getValue)) {
                    if ($getValue == $optionElement->value) {
                        $optionOptions['selected'] = 'selected';
                    }
                } else {
                    foreach ($getValue as $value) {
                        if ($value == $optionElement->value) {
                            $optionOptions['selected'] = 'selected';
                            break;
                        }
                    }
                }
            }
            if ($optionElement->disabled) {
                $optionOptions['disabled'] = 'disabled';
            }
            if ($optionElement->hidden) {
                $optionOptions['class'] = 'hide';
            } else {
                $size++;
            }

            $htmlTagDecorator = new Zend_Form_Decorator_HtmlTag();
            $htmlTagDecorator->setOptions($optionOptions);
            $option = $htmlTagDecorator->render($optionElement->label);

            $content .= $option;
        }

        if ($this->size !== null) {
            $size = $this->size;
        }
        $selectOptions = array(
            'tag'      => 'select',
            'id'       => $this->getId(),
            'name'     => $this->getName() . '[]',
            'size'     => $size,
            'multiple' => 'multiple',
            'class'    => $this->getAttrib('class'),
        );
        if ($this->getElement()->disabled) {
            $selectOptions['disabled'] = 'disabled';
        }
        if ($this->isRequired()) {
            $selectOptions['required'] = 'required';
        }

        $htmlTagDecorator = new Zend_Form_Decorator_HtmlTag();
        $htmlTagDecorator->setOptions($selectOptions);
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
        if ($this->useAutocomplete === true) {
            $autocomplete = new UI_JS_AutoComplete($this->getId(), array());
            return $autocomplete->getScript();
        }
        return '';
    }
}
