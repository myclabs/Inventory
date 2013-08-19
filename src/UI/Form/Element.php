<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * Element class
 * An Element populate a form with html input tag
 *
 * @author MyC.Sense
 * @package UI
 * @subpackage Form
 */
class UI_Form_Element
{
    /**
     * Element de Zend_Form correspondant.
     *
     * @var Zend_Form_Element|UI_Form_ZendElement
     */
    protected $_zendFormElement = null;

    /**
     * If true, the Element is not shown
     *
     * @var bool = false
     */
    public $hidden = false;

    /**
     * If true, the Element is disabled, but can become enabled with a user action
     *
     * @var bool = false
     */
    public $disabled = false;

    /**
     * Help message, shown in a popup
     *
     * @var string
     */
    public $help;

    /**
     * Message display before the element
     *
     * @var array
     */
    protected $_prefix = array();

    /**
     * Message display after the element
     *
     * @var array
     */
    protected $_suffix = array();

    /**
     * List of children elements
     *
     * @var UI_Form_ZendElement[]
     */
    public $children = array();

    /**
     * Direct group parent
     *
     * @var UI_Form_Element_Group
     */
    public $parent = null;

    /**
     * List of Actions
     *
     * @var UI_Form_Action[]
     */
    protected $_actions = array();

    /**
     * Constructor
     *
     * @param Zend_Form_Element $zendFormElement
     */
    public function __construct(Zend_Form_Element $zendFormElement)
    {
        $this->_zendFormElement = $zendFormElement;
    }

    /**
     * Initialize an element
     *     Adds decorators
     *     Adds attributes
     *
     * @return void
     */
    public function init()
    {
        // Disabled.
        if ($this->disabled) {
            $this->_zendFormElement->setAttrib('disabled', 'disabled');
        }
        // Required.
        if ($this->_zendFormElement->isRequired()) {
            $this->_zendFormElement->setAttrib('required', 'required');
        }

        $this->setDefaultDecorators();
    }

    /**
     * Ajoute un prefixe au ref
     * nouveau ref = prefix + separator + ref
     *
     * @param string $prefix
     * @param string $separator Séparateur entre le préfixe et le ref
     */
    public function prefixRef($prefix, $separator = UI_Generic::REF_SEPARATOR)
    {
        $this->_zendFormElement->setName($prefix . $separator . $this->_zendFormElement->getName());
        // Groupe
        foreach ($this->children as $child) {
            $child->getElement()->prefixRef($prefix, $separator);
        }
        // Groupe répété
        if ($this->_zendFormElement instanceof UI_Form_Element_GroupRepeated) {
            foreach ($this->_zendFormElement->getLineValues() as $group) {
                $group->getElement()->prefixRef($prefix);
            }
        }
    }

    /**
     * Ajoute un prefix.
     *
     * @param string $prefix
     */
    public function addPrefix($prefix)
    {
        $this->_prefix[] = $prefix;
    }

    /**
     * Indique si l'élément à au moins un prefix.
     *
     * @return boolean
     */
    public function hasPrefix()
    {
        return (count($this->_prefix) > 0);
    }

    /**
     * Renvoi les prefix de l'élément.
     *
     * @return array
     */
    public function getPrefix()
    {
        return $this->_prefix;
    }

    /**
     * Ajoute un suffix.
     *
     * @param string $suffix
     */
    public function addSuffix($suffix)
    {
        $this->_suffix[] = $suffix;
    }

    /**
     * Indique si l'élément à au moins un suffix.
     *
     * @return boolean
     */
    public function hasSuffix()
    {
        return (count($this->_suffix) > 0);
    }

    /**
     * Renvoi les suffix de l'élément.
     *
     * @return array
     */
    public function getSuffix()
    {
        return $this->_suffix;
    }

    /**
     * Add an action to the Element.
     *
     * @param UI_Form_Action $action
     * @return void
     */
    public function addAction(UI_Form_Action $action)
    {
        $this->_actions[] = $action;
    }

    /**
     * Get all the actions of the Element.
     *
     * @return UI_Form_Action[]
     */
    public function getActions()
    {
        return $this->_actions;
    }

    /**
     * Add an element on the same line of the current element.
     *
     * @param Zend_Form_Element $element
     * @return void
     */
    public function addElement($element)
    {
        $this->children[] = $element;
        $element->getElement()->parent = $this->_zendFormElement;
    }

    /**
     * Définit l'élément comme étant en lecture seule
     */
    public function setReadOnly()
    {
        $this->_zendFormElement->setAttrib('readonly', 'readonly');
        foreach ($this->children as $child) {
            $child->getElement()->setReadOnly();
        }
        // Select
        if ($this->_zendFormElement instanceof UI_Form_Element_MultiCheckbox
            || $this->_zendFormElement instanceof UI_Form_Element_Radio
            || $this->_zendFormElement instanceof UI_Form_Element_Select
            || $this->_zendFormElement instanceof UI_Form_Element_MultiSelect) {
            foreach ($this->_zendFormElement->getOptions() as $option) {
                $option->disabled = true;
            }
        }
    }

    /**
     * Set decorators by default to an Element
     */
    private function setDefaultDecorators()
    {
        //Clear
        $this->_zendFormElement->removeDecorator('Description');
        $this->_zendFormElement->removeDecorator('DtDdWrapper');
        $this->_zendFormElement->removeDecorator('Errors');
        $this->_zendFormElement->removeDecorator('HtmlTag');
        $hasLabelDecorator = ($this->_zendFormElement->getDecorator('Label') !== false) ? true : false;
        $this->_zendFormElement->removeDecorator('Label');
        $viewHelper = $this->_zendFormElement->getDecorator('ViewHelper');
        if ($viewHelper !== false) {
            $viewHelper->setOption('separator', '');
        }

        $this->_zendFormElement->addPrefixPath(
                'UI_Form_Decorator',
                dirname(__FILE__).'/Decorator/',
                Zend_Form_Element::DECORATOR
        );

        if ($this->_zendFormElement instanceof UI_Form_Element_Multi) {
            $decorators = $this->_zendFormElement->getDecorators();
            $this->_zendFormElement->clearDecorators();
            foreach ($decorators as $decorator) {
                if (!($decorator instanceof Zend_Form_Decorator_ViewHelper)) {
                    $this->_zendFormElement->addDecorator($decorator);
                }
            }
        }

        if (($this->parent->getDecorator('ActionGroup') === false)) {
            // Prefix.
            if ($this->hasPrefix()) {
                $this->_zendFormElement->addDecorator('Prefix');
            }

            // Suffix.
            if ($this->hasSuffix()) {
                $this->_zendFormElement->addDecorator('Suffix');
            }

            $this->_zendFormElement->addDecorator('InputAppend');

            if (!(empty($this->children))) {
                $this->_zendFormElement->addDecorator('Children');
            }

            // Description.
            $description = $this->_zendFormElement->getDescription();
            $messages = $this->_zendFormElement->getMessages();
            if ((isset($description)) || (!(empty($messages)))) {
                $this->_zendFormElement->addDecorator('HelpBlock');
            }

            // Controls.
            $this->_zendFormElement->addDecorator('Controls');

            // Help.
            if ($this->help) {
                $this->_zendFormElement->addDecorator('Help', array('escape' => false));
            }

            // Label.
            if ($hasLabelDecorator) {
                $this->_zendFormElement->addDecorator('Label', array('escape' => false));
                $labelDecorator = $this->_zendFormElement->getDecorator('Label');
                $labelDecorator->setTag('');
                if (preg_match('#control-label#', $labelDecorator->getOption('class')) == 0) {
                    $labelDecorator->setOption('class', $labelDecorator->getOption('class').' control-label');
                }
            }

            // Line.
            $this->_zendFormElement->addDecorator('Line');
        }
    }

    /**
     * Utilisé par getScript de Form.
     *
     * @return string
     */
    public function getScript()
    {
        $script = '';

        if ($this->help != null) {
            $script .= '$(\'#'.$this->_zendFormElement->getId().'-help\')';
            $script .= '.popover({trigger:\'hover\', placement:\'top\', html:true, container:\'body\'});';
        }

        $script .= $this->_zendFormElement->getScript();

        foreach ($this->getActions() as $action) {
            $script .= $action->getScript($this->_zendFormElement);
        }

        foreach ($this->children as $child) {
            $script .= $child->getElement()->getScript();
        }

        return $script;
    }

    /**
     * Utilisé par getScript de Form, utile pour reset l'état du formulaire éventuellement modifié par les actions.
     *
     * @return string
     */
    public function getResetScript()
    {
        $script = '';

        if ($this->_zendFormElement instanceof UI_Form_Element_Group) {
            $elementId = $this->_zendFormElement->getId();
        } else if (!($this->parent instanceof UI_Form_Element_Group)) {
            $elementId = $this->_zendFormElement->getId().'_child';
        } else {
            $elementId = $this->_zendFormElement->getId().'-line';
        }

        if ($this->hidden) {
            $script .= 'if (!$(\'#'.$elementId.'\').hasClass(\'hide\')) {';
            $script .= '$(\'#'.$elementId.'\').addClass(\'hide\')';
            $script .= '}';
        } else {
            $script .= 'if ($(\'#'.$elementId.'\').hasClass(\'hide\')) {';
            $script .= '$(\'#'.$elementId.'\').removeClass(\'hide\')';
            $script .= '}';
        }

        if ($this->_zendFormElement instanceof UI_Form_Element_Multi) {
            foreach ($this->_zendFormElement->getOptions() as $options) {
                $optionId = $this->_zendFormElement->getId().'_'.$options->value;
                if ($options->disabled) {
                    $script .= 'if ($(\'#'.$optionId.'\').attr(\'disabled\') == undefined) {';
                    $script .= '$(\'#'.$optionId.'\').attr(\'disabled\', "disabled")';
                    $script .= '}';
                } else {
                    $script .= 'if ($(\'#'.$optionId.'\').attr(\'disabled\') != undefined) {';
                    $script .= '$(\'#'.$optionId.'\').removeAttr(\'disabled\')';
                    $script .= '}';
                }

                if ($options->hidden) {
                    $script .= 'if (!$(\'#'.$optionId.'\').hasClass(\'hide\')) {';
                    $script .= '$(\'#'.$optionId.'\').addClass(\'hide\')';
                    $script .= '}';
                } else {
                    $script .= 'if ($(\'#'.$optionId.'\').hasClass(\'hide\')) {';
                    $script .= '$(\'#'.$optionId.'\').removeClass(\'hide\')';
                    $script .= '}';
                }
            }
        } else {
            if ($this->disabled) {
                $script .= 'if ($(\'#'.$this->_zendFormElement->getId().'\').attr(\'disabled\') == undefined) {';
                $script .= '$(\'#'.$this->_zendFormElement->getId().'\').attr(\'disabled\', "disabled")';
                $script .= '}';
            } else {
                $script .= 'if ($(\'#'.$this->_zendFormElement->getId().'\').attr(\'disabled\') != undefined) {';
                $script .= '$(\'#'.$this->_zendFormElement->getId().'\').removeAttr(\'disabled\')';
                $script .= '}';
            }
        }

        foreach ($this->children as $child) {
            $script .= $child->getElement()->getResetScript();
        }

        return $script;
    }

    /**
     * Utilisé par getScript de Form, utile pour appliquer une fonction en cas d'erreur.
     *
     * @return string
     */
    public function getErrorScript()
    {
        $script = '';

        if ($this->_zendFormElement instanceof UI_Form_Element_Captcha) {
            $elementId = $this->_zendFormElement->getId();
            $script .= '$.fn.changeImage'.$elementId.'();';
        }

        foreach ($this->children as $child) {
            $script .= $child->getElement()->getErrorScript();
        }

        return $script;
    }

    /**
     * @return UI_Form_ZendElement[]
     */
    public function getChildrenElements()
    {
        return $this->children;
    }
}
