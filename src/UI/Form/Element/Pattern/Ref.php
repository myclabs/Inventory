<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * Generate an input type of ref input text.
 * @package UI
 * @subpackage Form
 */
class UI_Form_Element_Pattern_Ref extends UI_Form_Element_Text
{
    /**
     * Construct
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name);

        $this->setAttrib('pattern', '^[a-z0-9_]+$');
    }

}