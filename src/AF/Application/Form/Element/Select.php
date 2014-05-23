<?php

namespace AF\Application\Form\Element;

use Core_Exception_InvalidArgument;
use UI_JS_AutoComplete;
use Zend_Form_Decorator_HtmlTag;
use Zend_Form_Element_Select;
use Zend_View_Interface;

/**
 * Generate a submit input
 *
 * @author valentin.claras
 */
class Select extends Zend_Form_Element_Select implements MultiElement, ZendFormElement
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

    /**
     * Get the associated Element.
     *
     * @return FormElement
     */
    public function getElement()
    {
        return $this->_element;
    }

    public function render(Zend_View_Interface $view = null)
    {
        $content = '';
        foreach ($this->_options as $optionElement) {
            $idOption = $this->getId() . '_' . $optionElement->value;
            $optionOptions = array(
                'tag'   => 'option',
                'id'    => $idOption,
                'value' => $optionElement->value
            );

            $getValue = $this->getValue();
            if ((isset($getValue)) && ($getValue === $optionElement->value)) {
                $optionOptions['selected'] = 'selected';
            }
            if ($optionElement->disabled) {
                $optionOptions['disabled'] = 'disabled';
            }
            if ($optionElement->hidden) {
                $optionOptions['class'] = 'hide';
            }

            $htmlTagDecorator = new Zend_Form_Decorator_HtmlTag();
            $htmlTagDecorator->setOptions($optionOptions);
            $option = $htmlTagDecorator->render($optionElement->label);

            $content .= $option;
        }

        $selectOptions = array(
            'tag'   => 'select',
            'id'    => $this->getId(),
            'name'  => $this->getName(),
            'class' => $this->getAttrib('class'),
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
