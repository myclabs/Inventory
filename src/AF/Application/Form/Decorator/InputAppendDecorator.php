<?php

namespace AF\Application\Form\Decorator;

use AF\Application\Form\Element\Checkbox;
use AF\Application\Form\Element\MultiCheckbox;
use AF\Application\Form\Element\Radio;
use AF\Application\Form\Element\Textarea;
use Zend_Form_Decorator_Abstract;
use Zend_Form_Decorator_HtmlTag;

/**
 * Add div tag with inpt class around an Element.
 *
 * @author valentin.claras
 */
class InputAppendDecorator extends Zend_Form_Decorator_Abstract
{
    public function render($content)
    {
        $element = $this->getElement();

        $options = array(
            'tag'   => 'div',
            'class' => 'input',
        );
        if ($element->getDecorator('PrefixDecorator') !== false) {
            $options['class'] .= ' input-prepend';
        }
        if ($element->getDecorator('SuffixDecorator') !== false) {
            $options['class'] .= ' input-append';
        }

        if ($element instanceof Textarea) {
            $options['class'] .= ' large';
        }

        // Pour les éléments childrens. Les options hide sont affichés dans l'input decorator.
        if (($element->getDecorator('LineDecorator') === false)
            && ($element->getDecorator('ControlsDecorator') === false)
        ) {
            if ($element->getElement()->hidden) {
                $options['class'] .= ' hide';
            }
            $options['id'] = $element->getId() . '_child';
        }

        if (($element instanceof Radio)
            || ($element instanceof MultiCheckbox)
            || ($element instanceof Checkbox)
        ) {
            $options['style'] = 'margin-top:5px;';
        }

        $decorator = new Zend_Form_Decorator_HtmlTag();
        $decorator->setOptions($options);

        return $decorator->render($content);
    }
}
