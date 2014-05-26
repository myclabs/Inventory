<?php

namespace AF\Application\Form\Decorator;

use AF\Application\Form\Element\RepeatedGroup;
use MyCLabs\MUIH\Collapse;
use Zend_Form_Decorator_Abstract;
use Zend_Form_Decorator_HtmlTag;

/**
 * Add div tag around a Group
 *
 * @author valentin.claras
 */
class GroupDecorator extends Zend_Form_Decorator_Abstract
{
    public function render($content)
    {
        if (($this->getElement()->getDecorator('MainGroupDecorator') !== false)
            || ($this->getElement()->getDecorator('ActionGroupDecorator') !== false)
        ) {
            return $content;
        }

        if ($this->getElement()->foldaway == true) {
            $collapse = new Collapse(
                $this->getElement()->getId() . '_wrapper',
                $this->getElement()->getLabel(),
                $content
            );
            if (!$this->getElement()->folded) {
                $collapse->show();
            }
            $collapse->addClass('subGroup');
            if ($this->getElement() instanceof RepeatedGroup) {
                $collapse->addClass('repeatedGroup');
            }
            foreach ($this->getElement()->getAttributes() as $name => $value) {
                $collapse->setAttribute($name, $value);
            }

            if ($this->getElement()->isHidden()) {
                $collapse->addClass('hide');
            }

            $collapse->setAttribute('id', $this->getElement()->getId());

            $content = $collapse->getHTML();
        } else {
            $groupOptions = array(
                'tag'   => 'fieldset',
                'class' => 'wrapper subGroup',
                'id'    => $this->getElement()->getId()
            );
            if ($this->getElement() instanceof RepeatedGroup) {
                $groupOptions['class'] .= ' repeatedGroup';
            }
            foreach ($this->getElement()->getAttributes() as $name => $value) {
                if ($name == 'class') {
                    $groupOptions['class'] .= ' ' . $value;
                } else {
                    $groupOptions[$name] = $value;
                }
            }
            $wrapperOptions = array(
                'tag' => 'div',
                'id'  => $this->getElement()->getId() . '_wrapper'
            );
            $headOptions = array(
                'tag' => 'legend',
            );
            if ($this->getElement()->isHidden()) {
                $groupOptions['class'] .= ' hide';
            }

            // Head.
            $htmlTagDecorator = new Zend_Form_Decorator_HtmlTag();
            $htmlTagDecorator->setOptions($headOptions);
            $head = $htmlTagDecorator->render($this->getElement()->getLabel());

            // Wrapper.
            $htmlTagDecorator = new Zend_Form_Decorator_HtmlTag();
            $htmlTagDecorator->setOptions($wrapperOptions);
            $wrapper = $htmlTagDecorator->render($content);

            // Group.
            $htmlTagDecorator = new Zend_Form_Decorator_HtmlTag();
            $htmlTagDecorator->setOptions($groupOptions);
            $content = $htmlTagDecorator->render($head . $wrapper);
        }

        return $content;
    }
}
