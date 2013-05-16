<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */


/**
 * Generate a span to display the error message
 * @author yoann.croizer
 * @package UI
 * @subpackage Form
 */
class UI_Form_Element_Pattern_Error extends UI_Form_Element_HTML
{
    /**
     * Construct
     *
     * @param string $name
     * @param string $class
     */
    public function __construct($name, $class='help-block')
    {
        $content = '<span id="'.$name.'" class="'.$class.'">' . '<br>' . $this->content . '</span>';
        parent::__construct($name, $content, true);
    }
}