<?php
/**
 * @author  matthieu.napoli
 * @package User
 */

/**
 * Adapter pour le système d'authentification Zend_Auth
 * @package User
 */
class User_AuthAdapter implements Zend_Auth_Adapter_Interface
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
            $user = User_Model_User::login($this->login, $this->password);
            if ($user->isEnabled()) {
                return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $user->getId(),
                                            [__('User', 'login', 'authentificationSuccess')]);
            } else {
                return new Zend_Auth_Result(Zend_Auth_Result::FAILURE, null,
                                            [__('User', 'login', 'accountDisabled')]);
            }
        } catch (Core_Exception_NotFound $e) {
            return new Zend_Auth_Result(Zend_Auth_Result::FAILURE, null, [__('User', 'login', 'unknownEmail')]);
        } catch (Core_Exception_InvalidArgument $e) {
            return new Zend_Auth_Result(Zend_Auth_Result::FAILURE, null, [__('User', 'login', 'invalidPassword')]);
        }
    }

}
