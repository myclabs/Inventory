<?php

namespace AF\Application\Form\Decorator;

use AF\Application\Form\Element\Group;
use Core_Tools;
use Zend_Form_Decorator_Abstract;
use Zend_Form_Decorator_HtmlTag;

/**
 * Add an Help popup before an Element.
 *
 * @author valentin.claras
 */
class HelpDecorator extends Zend_Form_Decorator_Abstract
{
    public function render($content)
    {
        $text = Core_Tools::textile($this->getElement()->getElement()->help);
        $aOptions = array(
            'tag'          => 'a',
            'id'           => $this->getElement()->getId() . '-help',
            'link'         => '#',
            'onMouseout'   => '$(this).children(\'.fa-question-circle\').css(\'opacity\', 0.3);',
            'onMouseover'  => '$(this).children(\'.fa-question-circle\').css(\'opacity\', 1);',
            'rel'          => 'popover',
            'data-content' => 'helpWillBeReplace',
            'style'        => 'display: inline-block; margin-left: 5px; padding-top: -5px;',
        );

        $iOptions = array(
            'tag'   => 'i',
            'class' => 'fa fa-question-circle',
            'style' => 'opacity: 0.3;'
        );

        $htmlTagDecorator = new Zend_Form_Decorator_HtmlTag();
        $htmlTagDecorator->setOptions($iOptions);
        $i = $htmlTagDecorator->render(' ');

        $htmlTagDecorator = new Zend_Form_Decorator_HtmlTag();
        $htmlTagDecorator->setOptions($aOptions);
        $a = $htmlTagDecorator->render($i);
        // Nécessaire pour pouvoir afficher des caractères HTML qui Zend échappe systématiquement.
        $a = str_replace('helpWillBeReplace', str_replace('"', '\'', $text), $a);

        if ($this->getElement() instanceof Group) {
            $smallOptions = array(
                'tag'   => 'small',
                'style' => 'color: #333333;',
            );
            $htmlTagDecorator = new Zend_Form_Decorator_HtmlTag();
            $htmlTagDecorator->setOptions($smallOptions);
            $a = $htmlTagDecorator->render($a);
        }

        $this->getElement()->setLabel($this->getElement()->getLabel() . $a);

        return $content;
    }
}
