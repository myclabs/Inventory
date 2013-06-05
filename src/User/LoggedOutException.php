<?php
/**
 * @author     matthieu.napoli
 * @package    User
 * @subpackage Exception
 */

namespace User;

use Core_Exception_User;

/**
 * The user is logged out, and needs to be redirected to log-in page
 *
 * @package    User
 * @subpackage Exception
 *
 * @see        Core_Exception_User
 */
class LoggedOutException extends Core_Exception_User
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('User', 'exceptions', 'mustBeLoggedIn');
    }

}
