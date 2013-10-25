<?php

namespace User\Application;

use User\Domain\User;
use Core_Exception_NotFound;
use Zend_Auth_Adapter_Interface;
use Zend_Auth_Adapter_Exception;
use Core_Exception_InvalidArgument;
use Zend_Auth_Result;

/**
 * Adapter pour le système d'authentification Zend_Auth.
 *
 * @author matthieu.napoli
 */
class AuthAdapter implements Zend_Auth_Adapter_Interface
{
    protected $login;
    protected $password;

    /**
     * Définition de l'identifiant et du mot de passe pour authentification
     *
     * @param string $login
     * @param string $password
     */
    public function __construct($login, $password)
    {
        $this->login = $login;
        $this->password = $password;
    }

    /**
     * Réalise une tentative d'authentification
     *
     * @throws Zend_Auth_Adapter_Exception Si l'authentification ne peut pas être réalisée
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        try {
            $user = User::login($this->login, $this->password);
            if ($user->isEnabled()) {
                return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $user->getId());
            } else {
                return new Zend_Auth_Result(
                    Zend_Auth_Result::FAILURE,
                    null,
                    [__('User', 'login', 'accountDisabled')]
                );
            }
        } catch (Core_Exception_NotFound $e) {
            return new Zend_Auth_Result(Zend_Auth_Result::FAILURE, null, [__('User', 'login', 'unknownEmail')]);
        } catch (Core_Exception_InvalidArgument $e) {
            return new Zend_Auth_Result(Zend_Auth_Result::FAILURE, null, [__('User', 'login', 'invalidPassword')]);
        }
    }
}
