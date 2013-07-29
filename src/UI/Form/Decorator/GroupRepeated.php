<?php
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
class UI_Form_Decorator_GroupRepeated extends Zend_Form_Decorator_Abstract
{
    /**
     * @param string $content
     * @see Zend/Form/Decorator/Zend_Form_Decorator_Abstract::render()
     */
    public function render($content)
    {
        $occurrenceSeparator = '__';
        $occurrence = 0;

        // Tags.
        $headerRowOptions = array(
            'tag' => 'tr',
        );
        $htmlHeaderRowTagDecorator = new Zend_Form_Decorator_HtmlTag();
        $htmlHeaderRowTagDecorator->setOptions($headerRowOptions);
        $headerOptions = array(
            'tag'   => 'th',
        );
        $elementsRowOptions = array(
            'tag' => 'tr',
            'id'  => $this->getElement()->getId().$occurrenceSeparator.$occurrence,
        );
        $htmlElementsRowTagDecorator = new Zend_Form_Decorator_HtmlTag();
        $htmlElementsRowTagDecorator->setOptions($elementsRowOptions);
        $elementOptions = array(
            'tag'   => 'td',
        );
        $htmlElementTagDecorator = new Zend_Form_Decorator_HtmlTag();
        $htmlElementTagDecorator->setOptions($elementOptions);
        $deleteButton = new UI_HTML_Button(__('UI', 'verb', 'delete'));
        $deleteButton->addAttribute('class', 'deleteRow');


        // Header.
        $baseElementsRow = '';
        $header = '';
        $htmlHeaderTagDecorator = new Zend_Form_Decorator_HtmlTag();
        $htmlHeaderTagDecorator->setOptions($headerOptions);
        foreach ($this->getElement()->getElement()->children as $childZendElement) {
            /**
             * @var Zend_Form_Element $childZendElement
             */
            $childZendElement->init();

            if ($childZendElement instanceof UI_Form_Element_Textarea) {
                $childZendElement->setAttrib('rows', 1);
            }

            // Suppression des décorateurs inutiles.
            $childZendElement->removeDecorator('line');
            $childZendElement->removeDecorator('label');
            $childZendElement->removeDecorator('help');

            $header .= $htmlHeaderTagDecorator->render($childZendElement->getLabel());

            // Base Element Row
            $baseName = $childZendElement->getName();
            $baseId = $childZendElement->getId();

            $childZendElement->setName($baseName.$occurrenceSeparator.$occurrence);
            $childZendElement->setAttrib('id', $baseId.$occurrenceSeparator.$occurrence);
            $htmlElementTagDecorator->setOption('id', $childZendElement->getId().'-line');
            $baseElementsRow .= $htmlElementTagDecorator->render($childZendElement->render());
            $htmlElementTagDecorator->removeOption('id');

            // Réinitialisation de l'élément.
            $childZendElement->setName($baseName);
            $childZendElement->setAttrib('id', $baseId);
        }
        // Delete header
        $header .= $htmlHeaderTagDecorator->render('');
        $headerRow = $htmlHeaderRowTagDecorator->render($header);
        // Base Delete
        $baseElementsRow .= $htmlElementTagDecorator->render($deleteButton->render());
        $baseElementsRow = $htmlElementsRowTagDecorator->render($baseElementsRow);

        $occurrence++;

        // ElementsRow.
        $elementsRow = '';
        foreach ($this->getElement()->getLineValues() as $lineValue) {
            $elements = '';
            foreach ($this->getElement()->getElement()->children as $childZendElement) {
                /**
                 * @var Zend_Form_Element $childZendElement
                 */
                $baseId = $childZendElement->getId();
                $baseName = $childZendElement->getName();
                $baseValue = $childZendElement->getValue();
                if ($childZendElement instanceof UI_Form_Element_Pattern_Value) {
                    $basePercentValue = $childZendElement->getPercent()->getValue();
                }

                $childZendElement->setName($baseName.$occurrenceSeparator.$occurrence);
                $childZendElement->setAttrib('id', $baseId.$occurrenceSeparator.$occurrence);

                foreach ($lineValue->getElement()->children as $valueElement) {
                    /**
                     * @var Zend_Form_Element $valueElement
                     */
                    if ($baseId === $valueElement->getId()) {
                        $childZendElement->setValue($valueElement->getValue());
                        if ($childZendElement instanceof UI_Form_Element_Pattern_Value) {
                            $childZendElement->setPercentValue($valueElement->getPercent()->getValue());
                        }
                    }
                }

                $htmlElementTagDecorator->setOption('id', $childZendElement->getId().'-line');
                $elements .= $htmlElementTagDecorator->render($childZendElement->render());
                $htmlElementTagDecorator->removeOption('id');

                // Réinitialisation de l'élément.
                $childZendElement->setName($baseName);
                $childZendElement->setAttrib('id', $baseId);
                $childZendElement->setValue($baseValue);
                if ($childZendElement instanceof UI_Form_Element_Pattern_Value) {
                    $childZendElement->getPercent()->setValue($basePercentValue);
                }
            }

            // Delete
            $elements .= $htmlElementTagDecorator->render($deleteButton->render());

            $htmlElementsRowTagDecorator->setOption('id', $this->getElement()->getId().$occurrenceSeparator.$occurrence);
            $elementsRow .= $htmlElementsRowTagDecorator->render($elements);
            $htmlElementsRowTagDecorator->removeOption('id');
            $occurrence++;
        }

        // Table.
        $tableOptions = array(
            'tag'   => 'table',
            'class' => 'table table-condensed repeatedGroup',
            'id'    => $this->getElement()->getId(),
        );
        $htmlTagDecorator = new Zend_Form_Decorator_HtmlTag();
        $htmlTagDecorator->setOptions($tableOptions);
        $content = $htmlTagDecorator->render($headerRow.$elementsRow);

        // Add Button
        $addButton = new UI_HTML_Button(__('UI', 'verb', 'add'));
        $addButton->addAttribute('id', $this->getElement()->getId().'_add');
        $addButton->addAttribute('class', 'addRow');
        $content .= $addButton->render();

        $addScript = '';
        $addScript .= 'var '.$this->getElement()->getId().'_nextRowId = '.$occurrence.';';
        $addScript .= '$(\'#'.$this->getElement()->getId().'_add'.'\').on(\'click\', function(e) {';
        $addScript .= '$(\'#'.$this->getElement()->getId().' tbody\').append(\''.$baseElementsRow.'\'.replace('.
            '/'.$occurrenceSeparator.'0/g, \''.$occurrenceSeparator.'\'+'.$this->getElement()->getId().'_nextRowId));';
        $addScript .= ''.$this->getElement()->getId().'_nextRowId++;';
        $addScript .= '});';
        $deleteScript = '';
        $deleteScript .= '$(\'#'.$this->getElement()->getId().'\').on(\'click\', \'.deleteRow\', function(e) {';
        $deleteScript .= '$(this).parent().parent().detach();';
        $deleteScript .= '});';

        $content .= '<script>'.$deleteScript.$addScript.'</script>';

        return $content;
    }

}
