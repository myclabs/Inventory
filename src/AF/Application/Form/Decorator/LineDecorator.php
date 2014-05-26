<?php

namespace AF\Application\Form\Decorator;

use Zend_Form_Decorator_Abstract;
use Zend_Form_Decorator_HtmlTag;

/**
 * Add div tag with form-group class around an Element.
 *
 * @author valentin.claras
 */
class LineDecorator extends Zend_Form_Decorator_Abstract
{
    public function render($content)
    {
        $element = $this->getElement();

        $options = array(
            'tag'   => 'div',
            'class' => 'form-group',
            'id'    => $this->getElement()->getId() . '-line'
        );

        if (count($this->getElement()->getMessages()) > 0) {
            $options['class'] .= ' warning';
        }

        if ($element->getElement()->hidden) {
            $options['class'] .= ' hide';
        }

        $decorator = new Zend_Form_Decorator_HtmlTag();
        $decorator->setOptions($options);

        return $decorator->render($content);
    }
}
