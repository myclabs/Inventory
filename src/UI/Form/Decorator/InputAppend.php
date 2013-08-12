<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * Add div tag with inpt class around an Element.
 *
 * @author MyC.Sense
 * @package UI
 * @subpackage Form
 */
class UI_Form_Decorator_InputAppend extends Zend_Form_Decorator_Abstract
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
            'class' => 'input',
        );
        if ($element->getDecorator('prefix') !== false) {
            $options['class'] .= ' input-prepend';
        }
        if ($element->getDecorator('suffix') !== false) {
            $options['class'] .= ' input-append';
        }

        if ($element instanceof UI_Form_Element_Pattern_Date) {
            $options['class'] .= ' date';
            if ($element->getValue() !== null) {
                $options['data-date'] = $element->getValue();
            } else {
                //@todo Date : Trouver un moyen de récupérer le format en fonction de la locale.
                $options['data-date'] = Core_Date::now()->formatDate('dd/MM/yyyy');
            }
        }

        if ($element instanceof UI_Form_Element_Textarea) {
            $options['class'] .= ' large';
        }

        // Pour les éléments childrens. Les options hide sont affichés dans l'input decorator.
        if (($element->getDecorator('line') === false)
            && ($element->getDecorator('controls') === false)) {
            if ($element->getElement()->hidden) {
                $options['class'] .= ' hide';
            }
            $options['id'] = $element->getId().'_child';
        }

        if (
            ($element instanceof UI_Form_Element_Radio)
            || ($element instanceof UI_Form_Element_MultiCheckbox)
            || ($element instanceof UI_Form_Element_Checkbox)
        ) {
            $options['style'] = 'margin-top:5px;';
        }

        $decorator = new Zend_Form_Decorator_HtmlTag();
        $decorator->setOptions($options);

        return $decorator->render($content);
    }
}