<?php

namespace User\Application;

use Core_Exception_User;

/**
 * The user is not allowed to perform the action
 *
 * @author matthieu.napoli
 * @see    Core_Exception_User
 */
class ForbiddenException extends Core_Exception_User
{
    public function __construct()
    {
        parent::__construct('User', 'genericException', 'accessForbidden');
    }
}
