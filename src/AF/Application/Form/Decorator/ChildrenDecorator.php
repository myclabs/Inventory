<?php

namespace AF\Application\Form\Decorator;

use Zend_Form_Decorator_Abstract;

/**
 * Append children to an Element.
 *
 * @author valentin.claras
 */
class ChildrenDecorator extends Zend_Form_Decorator_Abstract
{
    public function render($content)
    {
        foreach ($this->getElement()->getElement()->children as $childElement) {
            $childElement->getElement()->init();
            $childElement->removeDecorator('LineDecorator');
            $childElement->removeDecorator('ControlsDecorator');
            $content .= $childElement->render();
        }
        return $content;
    }
}
