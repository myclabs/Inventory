<?php
/**
 * @author     matthieu.napoli
 * @package    User
 * @subpackage Exception
 */

namespace User;

use Core_Exception_User;

/**
 * The user is not allowed to perform the action
 *
 * @package    User
 * @subpackage Exception
 *
 * @see        Core_Exception_User
 */
class ForbiddenException extends Core_Exception_User
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('User', 'genericException', 'accessForbidden');
    }

}