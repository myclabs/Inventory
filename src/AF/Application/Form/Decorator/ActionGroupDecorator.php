<?php

namespace AF\Application\Form\Decorator;

use Zend_Form_Decorator_Abstract;
use Zend_Form_Decorator_HtmlTag;

/**
 * Add div tag with form-actions class around an Element.
 *
 * @author valentin.claras
 */
class ActionGroupDecorator extends Zend_Form_Decorator_Abstract
{
    public function render($content)
    {
        $mainGroupOptions = array(
            'tag'   => 'div',
            'class' => 'form-actions'
        );

        $htmlTagDecorator = new Zend_Form_Decorator_HtmlTag();
        $htmlTagDecorator->setOptions($mainGroupOptions);

        return $htmlTagDecorator->render($content);
    }
}
