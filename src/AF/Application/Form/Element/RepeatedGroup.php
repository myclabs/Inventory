<?php

namespace AF\Application\Form\Element;

use Zend_Form_Element;

/**
 * Generate a group wich contains elements
 *
 * @author valentin.claras
 */
class RepeatedGroup extends Group
{
    /**
     * Ensemble des lignes de valeurs ajoutées par défaut.
     *
     * @var Group[]
     */
    protected $_lineValues = array();

    /**
     * @see Zend_Form_Element::loadDefaultDecorators()
     */
    public function loadDefaultDecorators()
    {
        $this->addPrefixPath(
            'AF\Application\Form\Decorator',
            dirname(__FILE__) . '/../Decorator/',
            Zend_Form_Element::DECORATOR
        );
        $this->clearDecorators();
        $this->addDecorator('RepeatedGroupDecorator');
        $this->addDecorator('GroupDecorator');
    }

    /**
     * Get the associated LineValues.
     *
     * @return Group[]
     */
    public function getLineValues()
    {
        return $this->_lineValues;
    }

    /**
     * Add a line of values to the default render
     *
     * @param Group $lineValue
     */
    public function addLineValue(Group $lineValue)
    {
        $this->_lineValues[] = $lineValue;
    }
}
