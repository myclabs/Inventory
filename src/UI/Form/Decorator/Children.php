<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * Append children to an Element.
 *
 * @author MyC.Sense
 * @package UI
 * @subpackage Form
 */
class UI_Form_Decorator_Children extends Zend_Form_Decorator_Abstract
{
    /**
     * @param string $content
     * @see Zend/Form/Decorator/Zend_Form_Decorator_Abstract::render()
     */
    public function render($content)
    {
        foreach ($this->getElement()->getElement()->children as $childElement) {
            $childElement->getElement()->init();
            $childElement->removeDecorator('line');
            $childElement->removeDecorator('controls');
            $content .= $childElement->render();
        }
        return $content;
    }
}