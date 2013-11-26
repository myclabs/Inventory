<?php

namespace User\Application;

use Core_Exception_User;

/**
 * The user is logged out, and needs to be redirected to log-in page
 *
 * @author matthieu.napoli
 * @see    Core_Exception_User
 */
class LoggedOutException extends Core_Exception_User
{
    public function __construct()
    {
        parent::__construct('User', 'exceptions', 'mustBeLoggedIn');
    }
}
