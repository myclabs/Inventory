<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * Add div tag with control-group class around an Element.
 *
 * @author MyC.Sense
 * @package UI
 * @subpackage Form
 */
class UI_Form_Decorator_Line extends Zend_Form_Decorator_Abstract
{
    /**
     * @param string $content
     * @see Zend/Form/Decorator/Zend_Form_Decorator_Abstract::render()
     */
    public function render($content)
    {
        $element = $this->getElement();

        $options = array(
            'tag'   => 'div',
            'class' => 'control-group',
            'id'    => $this->getElement()->getId().'-line'
        );

        $withdrawal = -1;
        /* @var $currentElement UI_Form_Element */
        $currentElement = $this->getElement()->getElement();
        while ($currentElement->parent !== null) {
            $currentZendElement = $currentElement->parent;
            $currentElement = $currentElement->parent->getElement();
            $hasDecorator = ($currentZendElement->getDecorator('Group') !== false) ? true : false;
            if ($hasDecorator) {
                $withdrawal++;
            }
        }
        if ($withdrawal > 0) {
            $options['class'] .= ' withdraw'.$withdrawal;
        }

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
