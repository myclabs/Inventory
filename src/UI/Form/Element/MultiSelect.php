<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * Generate a multiselect input.
 *
 * @package UI
 * @subpackage Form
 */
class UI_Form_Element_MultiSelect extends Zend_Form_Element_Multiselect implements UI_Form_Element_Multi
{
    /**
     * Reference to a UI_Form_Element, to access to its methods.
     *
     * @var UI_Form_Element
     */
    protected $_element;

    /**
     * List of options.
     *
     * @var UI_Form_Element_Option[]
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

        // Size of the multi
        $size = 0;
        foreach ($this->_options as $optionElement) {
            $idOption = $this->getId().'_'.$optionElement->value;
            $optionOptions = array(
                'tag'   => 'option',
                'id'    => $idOption,
                'value' => $optionElement->value
            );

            $getValue = $this->getValue();
            if (isset($getValue)) {
                if (!is_array($getValue)) {
                    if ($getValue === $optionElement->value) {
                        $optionOptions['selected'] = 'selected';
                    }
                } else {
                    foreach ($getValue as $value) {
                        if ($value === $optionElement->value) {
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
            'name'     => $this->getName().'[]',
            'size'     => $size,
            'multiple' => 'multiple'
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
     * Utilisé par UI_Form pour fournir les scripts javascripts.
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