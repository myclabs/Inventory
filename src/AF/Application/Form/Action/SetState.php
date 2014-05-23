<?php

namespace AF\Application\Form\Action;

use AF\Application\Form\Element\Option;
use Zend_Form_Element;

/**
 * An Action wich will change the state of an element.
 *
 * @author valentin.claras
 */
abstract class SetState extends FormAction
{
    /**
     * Indicate if a reversed action is called when the condition fail.
     *
     * @var string
     */
    public $withReverse = true;

    /**
     * The option inside a Multi Element which will be affected by the action instead of the whole element.
     *
     * @var Option
     */
    protected $_option = null;


    /**
     * Define the option inside a multi Element instead of the whole Element.
     *
     * @param Option $option
     */
    public function setOption(Option $option)
    {
        $this->_option = $option;
    }

    /**
     * @param Zend_Form_Element $element
     *
     * @return string
     */
    protected function getIdTargetedElement(Zend_Form_Element $element)
    {
        $id = $element->getId();
        if ($this->_option !== null) {
            $id .= '_' . $this->_option->value;
        }
        return $id;
    }

    /**
     * @return string
     */
    protected function getFunctionCalled()
    {
        return $this->_functionCalled . '()';
    }

    /**
     * @return bool
     */
    protected function hasReverseFunction()
    {
        return $this->withReverse;
    }

    /**
     * @return string
     */
    protected function getReverseFunctionCalled()
    {
        return $this->_reverseFunctionCalled . '()';
    }
}
