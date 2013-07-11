<?php
/**
 * @author matthieu.napoli
 * @package UI
 * @subpackage Form
 */

/**
 * Zend form element
 *
 * @package UI
 * @subpackage Form
 */
interface UI_Form_ZendElement
{
    /**
     * Get the associated UI Element.
     *
     * @return UI_Form_Element
     */
    public function getElement();
}
