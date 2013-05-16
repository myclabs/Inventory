<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * Options to add into a Multi Element.
 *
 * @package UI
 * @subpackage Form
 * @see UI_Form_Element_Multi
 */
class UI_Form_Element_Option
{
    /**
     * Unique reference of the Option
     *
     * @var string
     */
    public $ref;

    /**
     * Send value of the Option
     *
     * @var string | int
     */
    public $value = null;

    /**
     * Displayed label of the Option
     *
     * @var string
     */
    public $label = null;

    /**
     * If true, the Option is disabled
     *
     * @var bool = false
     */
    public $disabled = false;

    /**
     * If true, the option is hidden
     *
     * @var bool = false
     */
    public $hidden = false;


    /**
     * Constructor
     *
     * @param string $ref
     * @param string $value
     * @param string|int $label
     *
     * @throws Core_Exception_InvalidArgument if ref is not valid.
     */
    public function __construct($ref, $value = null, $label = null)
    {
        if ($ref === null || trim($ref) === '') {
            throw new Core_Exception_InvalidArgument('A ref attribute is required');
        }

        $this->ref      = $ref;
        $this->value    = $value;
        $this->label    = $label;
    }

}
