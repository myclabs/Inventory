<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * Generate a group wich contains elements
 *
 * @package UI
 * @subpackage Form
 */
class UI_Form_Element_GroupRepeated extends UI_Form_Element_Group
{
    /**
     * Ensemble des lignes de valeurs ajoutées par défaut.
     *
     * @var UI_Form_Element_Group[]
     */
    protected $_lineValues = array();

    /**
     * @see Zend/Form/Zend_Form_Element::loadDefaultDecorators()
     */
    public function loadDefaultDecorators()
    {
        $this->addPrefixPath(
            'UI_Form_Decorator',
            dirname(__FILE__).'/../Decorator/',
            Zend_Form_Element::DECORATOR
        );
        $this->clearDecorators();
        $this->addDecorator('GroupRepeated');
        $this->addDecorator('Group');
    }

    /**
     * Get the associated LineValues.
     *
     * @return UI_Form_Element_Group[]
     */
    public function getLineValues()
    {
        return $this->_lineValues;
    }

    /**
     * Add a line of values to the default render
     *
     * @param UI_Form_Element_Group $lineValue
     */
    public function addLineValue(UI_Form_Element_Group $lineValue)
    {
        $this->_lineValues[] = $lineValue;
    }

    /**
     * Render form element
     *
     * @param  Zend_View_Interface $view
     * @return string
     */
    public function render(Zend_View_Interface $view = null)
    {
        if ($this->_isPartialRendering) {
            return '';
        }

        if (null !== $view) {
            $this->setView($view);
        }

        $content = '';

        foreach ($this->_element->children as $child) {
            if ($child instanceof self) {
                $content .= $child->render($view);
            } else {
                $child->getElement()->init($child);
                $content .= $child->render();
            }
        }

        // Help.
        if ($this->getElement()->help) {
            $decorators = $this->getDecorators();
            $this->clearDecorators();
            $this->addDecorator('Help', array('escape' => false));
            $this->addDecorators($decorators);
        }
        // Décorators.
        foreach ($this->getDecorators() as $decorator) {
            $decorator->setElement($this);
            $content = $decorator->render($content);
        }

        return $content;
    }

    /**
     * Utilisé par UI_Form pour fournir les scripts javascripts.
     *
     * @return string
     */
    public function getScript()
    {
        $script = '';

        if ($this->foldaway == true) {
            $collapse = new UI_HTML_Collapse($this->getId());
            $collapse->foldedByDefault = $this->folded;

            if ($this->isHidden()) {
                $collapse->addAttribute('class', 'hide');
            }

            $script .= $collapse->getScript();
        }

        return $script;
    }

}
