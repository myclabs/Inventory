<?php
/**
 * @package    Core
 * @subpackage Exception
 */

/**
 * The user is the source of the error of the system
 *
 * The message is to be translated and displayed.
 *
 * @package    Core
 * @subpackage Exception
 */
class Core_Exception_User extends Core_Exception
{

    /**
     * Constructor
     *
     * Translate the message to be displayed.
     *
     * @param string $package     Package contenant la traduction
     * @param string $file        Fichier contenant la traduction
     * @param string $ref         Référence vers la traduction
     * @param array $replacements Remplacement dans la traduction
     */
    public function __construct($package, $file, $ref, $replacements=array())
    {
        $message = __($package, $file, $ref, $replacements);
        parent::__construct($message);
    }

}