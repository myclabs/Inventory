<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * Add fieldset tag around an Element.
 *
 * @package UI
 * @subpackage Form
 */
class UI_Form_Decorator_MainGroup extends Zend_Form_Decorator_Abstract
{
    /**
     * @param string $content
     * @see Zend/Form/Decorator/Zend_Form_Decorator_Abstract::render()
     */
    public function render($content)
    {
        $mainGroupOptions = array(
            'tag' => 'fieldset',
        );

        $htmlTagDecorator = new Zend_Form_Decorator_HtmlTag();
        $htmlTagDecorator->setOptions($mainGroupOptions);

        return $htmlTagDecorator->render($content);
    }
}