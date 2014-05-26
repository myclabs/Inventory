<?php

namespace AF\Application\Form\Condition;

use AF\Application\Form\Element\Checkbox;
use AF\Application\Form\Element\MultiSelect;
use AF\Application\Form\Element\Select;
use AF\Application\Form\Element\TextField;
use Core_Exception;
use AF\Application\Form\Element\MultiCheckbox;
use AF\Application\Form\Element\Pattern\ValuePattern;
use AF\Application\Form\Element\Radio;
use Zend_Form_Element;

/**
 * Used to associated conditions with actions
 *
 * @author valentin.claras
 */
class ElementaryCondition extends FormCondition
{
    /**
     * Type of Validator
     *
     * @var int
     */
    public $relation;

    /**
     * Element in wich the condition act
     *
     * @var Zend_Form_Element
     */
    public $element;

    /**
     * Value of the condition, according to the Element type
     *
     * @var mixed
     */
    public $value;

    /**
     * Constructor
     *
     * @param string            $ref
     * @param Zend_Form_Element $element
     * @param int               $relation
     * @param mixed             $value
     * @throws Core_Exception
     *             if $ref is unvalid
     */
    public function __construct($ref, $element = null, $relation = null, $value = null)
    {
        $this->element = $element;
        $this->relation = $relation;
        $this->value = $value;

        parent::__construct($ref);
    }

    /**
     * Render the javascript listener.
     *
     * @param string $actionRef unique reference of the action using the condition.
     *
     * @return String
     */
    public function getScriptListener($actionRef)
    {
        $scriptListener = '';

        if (($this->element instanceof ValuePattern) && (!(is_null($this->element->getPercent())))) {
            $scriptListener .= '$(\'#' . $this->element->getPercent()->getId() . '\')';
            $scriptListener .= '.change(formAction_' . $actionRef . ');';
        }

        if (($this->element instanceof Radio)
            || ($this->element instanceof MultiCheckbox)
        ) {
            foreach ($this->element->getOptions() as $option) {
                $scriptListener .= '$(\'#' . $this->element->getId() . '_' . $option->value . '\')';
                $scriptListener .= '.change(function(e) {';
                $scriptListener .= '$(\'#' . $this->element->getId() . '\').formAction_' . $actionRef . '();';
                $scriptListener .= '});';
            }
        } else {
            $scriptListener .= '$(\'#' . $this->element->getId() . '\')';
            $scriptListener .= '.change(function(e) {';
            $scriptListener .= '$(\'#' . $this->element->getId() . '\').formAction_' . $actionRef . '();';
            $scriptListener .= '});';
        }

        return $scriptListener;
    }

    /**
     * Render the javascript condition.
     *
     * @param string $actionRef unique reference of the action using the condition.
     *
     * @throws \Core_Exception
     * @return string
     */
    public function getScriptCondition($actionRef)
    {
        $scriptCondition = '';

        $jQueryId = '$(\'#' . $this->element->getId() . '\')';
        switch (get_class($this->element)) {
            case ValuePattern::class:
                // On test le cas ou un champ n'a pas été rempli (utile pour les condtions composées
                $scriptCondition .= 'parseInt(' . $jQueryId . '.val()) ' . $this->relation . ' ';
                if (empty($this->value)) {
                    $scriptCondition .= '""';
                } else {
                    $scriptCondition .= $this->value;
                }
                break;
            case TextField::class:
            case Select::class:
            case MultiSelect::class:
                $scriptCondition .= $jQueryId . '.val() ' . $this->relation . ' \'' . $this->value . '\'';
                break;
            case Checkbox::class:
                $checked = $this->value ? 'true' : 'false';
                $scriptCondition .= $jQueryId . '.prop(\'checked\') ' . $this->relation . ' ' . $checked;
                break;
            case Radio::class:
                $jQueryId = '$(\'#' . $this->element->getId() . '_' . $this->value . '\')';
                $scriptCondition .= $jQueryId . '.prop(\'checked\') ' . $this->relation . ' true';
                break;
            case MultiCheckbox::class:
                if ($this->value !== null) {
                    $jQueryId = '$(\'#' . $this->element->getId() . '_' . $this->value . '\')';
                    $scriptCondition .= $jQueryId.'.prop(\'checked\') ' . $this->relation . ' true';
                } else {
                    $jQueryId = '$(\'input[name=' . $this->element->getName() . ']:selected\')';
                    $scriptCondition .= $jQueryId . '.length ' . $this->relation . ' ' . $this->value;
                }
                break;
            default:
                throw new Core_Exception('Condition error');
        }

        return $scriptCondition;
    }

    /**
     * Render the javascript param.
     *
     * @param string $actionRef unique reference of the action using the condition.
     *
     * @return String
     */
    public function getScriptParams($actionRef)
    {
        $scriptParam = '';

        $jQueryId = '$(\'#' . $this->element->getId() . '\')';
        switch (get_class($this->element)) {
            case ValuePattern::class:
                $scriptParam .= $this->element->getId() . '=\' + ' . $jQueryId . '.val() + \'';
                if (!is_null($this->element->getPercent())) {
                    $scriptParam .= '&' . $this->element->getPercent()->getId()
                        . '=\' + $(\'#' . $this->element->getPercent()->getId() . '\').val() + \'';
                }
                break;
            case TextField::class:
            case Select::class:
            case MultiSelect::class:
                $scriptParam .= $this->element->getId() . '=\' + ' . $jQueryId . '.val()+\'';
                break;
            case Checkbox::class:
                $scriptParam .= $this->element->getId() . '=\' + ' . $jQueryId . '.attr(\'checked\') + \'';
                break;
            case Radio::class:
                $jQueryId = '$(\'#' . $this->element->getId() . $this->value . '\')';
                $scriptParam .= $this->element->getId() . $this->value . '=\' + ' . $jQueryId . '.attr(\'checked\') + \'';
                break;
            case MultiCheckbox::class:
                if ($this->value !== null) {
                    $jQueryId = '$(\'#' . $this->element->getId() . $this->value . '\')';
                    $scriptParam .= $this->element->getId() . $this->value
                        . '=\' + ' . $jQueryId . '.attr\'checked\') + \'';
                } else {
                    $jQueryId = '$(\'input[name=' . $this->element->getName() . ']:selected\')';
                    $scriptParam .= $this->element->getName() . '=\' + function(){';
                    $scriptParam .= 'for (var x = 0; key < ' . $jQueryId . '.length; key++) {';
                    $scriptParam .= 'if ($(' . $jQueryId . '[key]).attr(\'checked\') == \'checked\') {';
                    $scriptParam .= 'return $(' . $jQueryId . '[key]).val();';
                    $scriptParam .= '}';
                    $scriptParam .= '}';
                    $scriptParam .= '} + \'';
                }
                break;
            default:
        }

        return $scriptParam;
    }
}
