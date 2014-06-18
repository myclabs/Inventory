<?php

namespace AF\Application\Form\Decorator;

use Zend_Form_Decorator_Abstract;
use Zend_Form_Decorator_HtmlTag;

/**
 * Add a prefix add-on before an Element.
 *
 * @author valentin.claras
 */
class PrefixDecorator extends Zend_Form_Decorator_Abstract
{
    public function render($content)
    {
        $mainGroupOptions = array(
            'tag'   => 'span',
            'class' => 'add-on'
        );

        $htmlTagDecorator = new Zend_Form_Decorator_HtmlTag();
        $htmlTagDecorator->setOptions($mainGroupOptions);

        foreach ($this->getElement()->getElement()->getPrefix() as $prefix) {
            $content = $htmlTagDecorator->render($prefix) . $content;
        }

        return $content;
    }
}
