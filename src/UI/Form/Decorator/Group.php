<?php
use MyCLabs\MUIH\Collapse;

/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * Add div tag around a Group
 *
 * @author MyC.Sense
 * @package UI
 * @subpackage Form
 */
class UI_Form_Decorator_Group extends Zend_Form_Decorator_Abstract
{
    /**
     * @param string $content
     * @see Zend/Form/Decorator/Zend_Form_Decorator_Abstract::render()
     */
    public function render($content)
    {
        if (($this->getElement()->getDecorator('MainGroup') !== false)
            || ($this->getElement()->getDecorator('ActionGroup') !== false)) {
            return $content;
        }

        if ($this->getElement()->foldaway == true) {
            $collapse = new Collapse($this->getElement()->getId(), $this->getElement()->getLabel(), $content);
            $collapse->foldedByDefault = $this->getElement()->folded;
            $collapse->addClass('subGroup');
            if ($this->getElement() instanceof UI_Form_Element_GroupRepeated) {
                $collapse->addClass('repeatedGroup');
            }
            foreach ($this->getElement()->getAttributes() as $name => $value) {
                $collapse->setAttribute($name, $value);
            }

            if ($this->getElement()->isHidden()) {
                $collapse->addClass('hide');
            }

            $content = $collapse->getHTML();
        } else {
            $groupOptions = array(
                'tag'   => 'fieldset',
                'class' => 'wrapper subGroup',
                'id'    => $this->getElement()->getId()
            );
            if ($this->getElement() instanceof UI_Form_Element_GroupRepeated) {
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
                    'id'  => $this->getElement()->getId().'_wrapper'
                );
            $headOptions = array(
                    'tag'   => 'legend',
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
            $content = $htmlTagDecorator->render($head.$wrapper);
        }

        return $content;
    }

}
