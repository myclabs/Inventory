<?php
/**
 * @author     matthieu.napoli
 * @package    User
 * @subpackage Exception
 */

/**
 * The user is not allowed to perform the action
 *
 * @package    User
 * @subpackage Exception
 *
 * @see Core_Exception_User
 */
class User_Exception_Forbidden extends Core_Exception_User
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('User', 'genericException', 'accessForbidden');
    }

}