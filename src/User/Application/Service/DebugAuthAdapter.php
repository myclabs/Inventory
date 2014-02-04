<?php

namespace User\Application\Service;

use User\Domain\User;
use Zend_Auth_Adapter_Interface;
use Zend_Auth_Result;

/**
 * Adapter pour le système d'authentification Zend_Auth qui ne vérifie pas de mot de passe.
 *
 * @author matthieu.napoli
 */
class DebugAuthAdapter implements Zend_Auth_Adapter_Interface
{
    protected $email;

    public function __construct($email)
    {
        $this->email = $email;
    }

    public function authenticate()
    {
        $user = User::loadByEmail($this->email);

        return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $user->getId());
    }
}
