<?php

namespace User\Application;

use Core_Exception;
use Core_Exception_User;

/**
 * Page not found: 404.
 *
 * @author matthieu.napoli
 */
class HttpNotFoundException extends Core_Exception_User
{
    public function __construct($message = null)
    {
        $message = $message ?: __('Core', 'exception', 'pageNotFound');

        Core_Exception::__construct($message);
    }
}
