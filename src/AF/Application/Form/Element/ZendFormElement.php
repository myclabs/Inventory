<?php

namespace AF\Application\Form\Element;

/**
 * Zend form element
 *
 * @author matthieu.napoli
 */
interface ZendFormElement
{
    /**
     * Get the associated UI Element.
     *
     * @return FormElement
     */
    public function getElement();

    /**
     * Return element name
     *
     * @return string
     */
    public function getName();
}
