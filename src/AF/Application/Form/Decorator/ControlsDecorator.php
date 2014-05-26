<?php

namespace AF\Application\Form\Decorator;

use Zend_Form_Decorator_Abstract;
use Zend_Form_Decorator_HtmlTag;

/**
 * Add div tag with controls class around an Element.
 *
 * @author valentin.claras
 */
class ControlsDecorator extends Zend_Form_Decorator_Abstract
{
    public function render($content)
    {
        $options = [
            'tag'   => 'div',
            'class' => 'controls'
        ];

        $decorator = new Zend_Form_Decorator_HtmlTag();
        $decorator->setOptions($options);

        return $decorator->render($content);
    }
}
