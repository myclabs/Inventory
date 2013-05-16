<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * Add div tag with form-actions class around an Element.
 *
 * @package UI
 * @subpackage Form
 */
class UI_Form_Decorator_ActionGroup extends Zend_Form_Decorator_Abstract
{
    /**
     * @param string $content
     * @see Zend/Form/Decorator/Zend_Form_Decorator_Abstract::render()
     */
    public function render($content)
    {
        $mainGroupOptions = array(
            'tag'   => 'div',
            'class' => 'form-actions'
        );

        $htmlTagDecorator = new Zend_Form_Decorator_HtmlTag();
        $htmlTagDecorator->setOptions($mainGroupOptions);

        return $htmlTagDecorator->render($content);
    }
}