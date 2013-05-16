<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * Add div tag with controls class around an Element.
 *
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */
class UI_Form_Decorator_Controls extends Zend_Form_Decorator_Abstract
{
    /**
     * @param string $content
     * @see Zend/Form/Decorator/Zend_Form_Decorator_Abstract::render()
     */
    public function render($content)
    {
        $options = array(
            'tag'   => 'div',
            'class' => 'controls'
        );

        $decorator = new Zend_Form_Decorator_HtmlTag();
        $decorator->setOptions($options);

        return $decorator->render($content);
    }
}