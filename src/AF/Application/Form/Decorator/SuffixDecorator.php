<?php

namespace AF\Application\Form\Decorator;

use Zend_Form_Decorator_Abstract;
use Zend_Form_Decorator_HtmlTag;

/**
 * Add a suffix add-on before an Element.
 *
 * @author valentin.claras
 */
class SuffixDecorator extends Zend_Form_Decorator_Abstract
{
    public function render($content)
    {
        $mainGroupOptions = array(
            'tag'   => 'span',
            'class' => 'add-on'
        );

        $htmlTagDecorator = new Zend_Form_Decorator_HtmlTag();
        $htmlTagDecorator->setOptions($mainGroupOptions);

        foreach ($this->getElement()->getElement()->getSuffix() as $suffix) {
            $content = $content . $htmlTagDecorator->render($suffix);
        }

        return $content;
    }
}
