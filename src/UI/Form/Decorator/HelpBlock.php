<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * Add an Help-Block below an Element.
 *
 * @package UI
 * @subpackage Form
 */
class UI_Form_Decorator_HelpBlock extends Zend_Form_Decorator_Abstract
{
    /**
     * @param string $content
     * @see Zend/Form/Decorator/Zend_Form_Decorator_Abstract::render()
     */
    public function render($content)
    {
        $help = '';

        $helpBlockOptions = array(
                'tag'      => 'span',
                'class'    => 'help-block'
            );

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