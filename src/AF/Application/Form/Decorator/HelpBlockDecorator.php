<?php

namespace AF\Application\Form\Decorator;

use Zend_Form_Decorator_Abstract;
use Zend_Form_Decorator_HtmlTag;

/**
 * Add an Help-Block below an Element.
 *
 * @author valentin.claras
 */
class HelpBlockDecorator extends Zend_Form_Decorator_Abstract
{
    public function render($content)
    {
        $help = '';

        $helpBlockOptions = [
            'tag'   => 'span',
            'class' => 'col-sm-offset-3 help-block'
        ];

        $description = $this->getElement()->getDescription();
        $messages = $this->getElement()->getMessages();

        if (isset($description)) {
            $htmlTagDecorator = new Zend_Form_Decorator_HtmlTag();
            $htmlTagDecorator->setOptions($helpBlockOptions);
            $help .= $htmlTagDecorator->render($description);
        }

        $helpBlockOptions['class'] .= ' errorMessage';

        foreach ($messages as $message) {
            $htmlTagDecorator = new Zend_Form_Decorator_HtmlTag();
            $htmlTagDecorator->setOptions($helpBlockOptions);
            $help .= $htmlTagDecorator->render($message);
        }

        return $content . $help;
    }
}
