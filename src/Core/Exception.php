<?php
/**
 * @package    Core
 * @subpackage Exception
 */

/**
 * Exception base class
 *
 * @package    Core
 * @subpackage Exception
 */
class Core_Exception extends Exception
{

    /**
     * Simplifie le constructeur
     * @param string $message
     */
    public function __construct($message = null)
    {
        parent::__construct($message, null, null);
    }

}