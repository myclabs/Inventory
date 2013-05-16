<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * Add a prefix add-on before an Element.
 *
 * @author MyC.Sense
 * @package UI
 * @subpackage Form
 */
class UI_Form_Decorator_Prefix extends Zend_Form_Decorator_Abstract
{
    /**
     * @param string $content
     * @see Zend/Form/Decorator/Zend_Form_Decorator_Abstract::render()
     */
    public function render($content)
    {
        $mainGroupOptions = array(
            'tag' => 'span',
            'class' => 'add-on'
        );

        $htmlTagDecorator = new Zend_Form_Decorator_HtmlTag();
        $htmlTagDecorator->setOptions($mainGroupOptions);

        foreach ($this->getElement()->getElement()->getPrefix() as $prefix) {
            $content = $htmlTagDecorator->render($prefix) . $content;
        }

        return $content;
    }
}