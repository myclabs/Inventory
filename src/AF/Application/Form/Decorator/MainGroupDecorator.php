<?php

namespace AF\Application\Form\Decorator;

use Zend_Form_Decorator_Abstract;
use Zend_Form_Decorator_HtmlTag;

/**
 * Add fieldset tag around an Element.
 *
 * @author valentin.claras
 */
class MainGroupDecorator extends Zend_Form_Decorator_Abstract
{
    public function render($content)
    {
        $mainGroupOptions = array(
            'tag' => 'fieldset',
        );

        $htmlTagDecorator = new Zend_Form_Decorator_HtmlTag();
        $htmlTagDecorator->setOptions($mainGroupOptions);

        return $htmlTagDecorator->render($content);
    }
}
