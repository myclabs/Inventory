<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * Generate an input type of email input text.
 * @package UI
 * @subpackage Form
 */
class UI_Form_Element_Pattern_Email extends UI_Form_Element_Text
{
    /**
     * Construct
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name);

        $this->setLabel(__('UI', 'name', 'emailAddress'));
        $this->setAttrib('size', 50);
        $this->addValidator('EmailAddress');
    }
}