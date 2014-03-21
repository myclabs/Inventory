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
    const OCCURRENCE_SEPARATOR = '__';
    
    /**
     * @param string $content
     * @see Zend/Form/Decorator/Zend_Form_Decorator_Abstract::render()
     */
    public function render($content)
    {
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
            'id'  => $this->getElement()->getId().self::OCCURRENCE_SEPARATOR.$occurrence,
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
            $this->initZendElement($childZendElement, $occurrence);

            $header .= $htmlHeaderTagDecorator->render($childZendElement->getLabel());

            $htmlElementTagDecorator->setOption('id', $childZendElement->getId().'-line');
            $baseElementsRow .= $htmlElementTagDecorator->render($childZendElement->render());
            $htmlElementTagDecorator->removeOption('id');
        }
        // Header Delete
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
            foreach ($lineValue->getElement()->children as $childZendElement) {
                /**
                 * @var Zend_Form_Element $childZendElement
                 */
                $this->initZendElement($childZendElement, $occurrence);
                
                $htmlElementTagDecorator->setOption('id', $childZendElement->getId().'-line');
                $elements .= $htmlElementTagDecorator->render($childZendElement->render());
                $htmlElementTagDecorator->removeOption('id');
            }

            // Delete
            $elements .= $htmlElementTagDecorator->render($deleteButton->render());

            $htmlElementsRowTagDecorator->setOption('id', $this->getElement()->getId().self::OCCURRENCE_SEPARATOR.$occurrence);
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
        $addScript .= '$(\'#'.$this->getElement()->getId().' tbody\').append(\''.str_replace('\'', '\\\'', $baseElementsRow).'\'.replace('.
            '/'.self::OCCURRENCE_SEPARATOR.'0/g, \''.self::OCCURRENCE_SEPARATOR.'\'+'.$this->getElement()->getId().'_nextRowId));';
        $addScript .= ''.$this->getElement()->getId().'_nextRowId++;';
        $addScript .= '});';
        $deleteScript = '';
        $deleteScript .= '$(\'#'.$this->getElement()->getId().'\').on(\'click\', \'.deleteRow\', function(e) {';
        $deleteScript .= '$(this).parent().parent().detach();';
        $deleteScript .= '});';

        $content .= '<script>'.$deleteScript.$addScript.'</script>';

        $this->resetChildZendElements();

        return $content;
    }

    /**
     * @param Zend_Form_Element $zendElement
     * @param $occurrence
     */
    protected function initZendElement($zendElement, $occurrence)
    {
        if ($occurrence > 0) {
            $zendElement->getElement()->init();
        }

        if ($zendElement instanceof UI_Form_Element_Textarea) {
            $zendElement->setAttrib('rows', 1);
        }

        // Suppression des décorateurs inutiles.
        $zendElement->removeDecorator('line');
        $zendElement->removeDecorator('label');
        $zendElement->removeDecorator('help');

        $zendElement->setAttrib('id', $zendElement->getId().self::OCCURRENCE_SEPARATOR.$occurrence);
        $zendElement->setName($zendElement->getName().self::OCCURRENCE_SEPARATOR.$occurrence);

        foreach ($zendElement->getElement()->children as $childZendElement) {
            $childZendElement->setAttrib('id', $childZendElement->getId().self::OCCURRENCE_SEPARATOR.$occurrence);
            $childZendElement->setName($childZendElement->getName().self::OCCURRENCE_SEPARATOR.$occurrence);
        }
    }

    protected function resetChildZendElements()
    {
        foreach ($this->getElement()->getElement()->children as $childZendElement) {
            $childZendElement->setAttrib('id', substr_replace($childZendElement->getId(), '', strpos($childZendElement->getId(), self::OCCURRENCE_SEPARATOR.'0'), 3));
            $childZendElement->setName(substr_replace($childZendElement->getId(), '', strpos($childZendElement->getName(), self::OCCURRENCE_SEPARATOR.'0'), 3));
        }
    }

}
