<?php

namespace AF\Application\Form\Element;

use Core_Exception_InvalidArgument;

/**
 * Options to add into a Multi Element.
 *
 * @author valentin.claras
 */
class Option
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
     * @param string     $ref
     * @param string     $value
     * @param string|int $label
     *
     * @throws Core_Exception_InvalidArgument if ref is not valid.
     */
    public function __construct($ref, $value = null, $label = null)
    {
        if ($ref === null || trim($ref) === '') {
            throw new Core_Exception_InvalidArgument('A ref attribute is required');
        }

        $this->ref = $ref;
        $this->value = $value;
        $this->label = $label;
    }
}
