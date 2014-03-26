<?php

use Account\Application\Service\AccountViewFactory;
use Account\Domain\Account;
use Account\Domain\AccountRepository;
use User\Domain\User;

/**
 * Configure le menu de l'application.
 *
 * @author matthieu.napoli
 */
class Inventory_Plugin_MenuPlugin extends Zend_Controller_Plugin_Abstract
{
    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @var AccountViewFactory
     */
    private $accountViewFactory;

    public function __construct(AccountRepository $accountRepository, AccountViewFactory $accountViewFactory)
    {
        $this->accountRepository = $accountRepository;
        $this->accountViewFactory = $accountViewFactory;
    }

    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $user = $this->getLoggedInUser();
        if (! $user) {
            return;
        }

        /** @var Zend_View_Abstract $view */
        $view = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('view');

        // Tous les comptes que l'utilisateur peut voir
        $accounts = $this->accountRepository->getTraversableAccounts($user);
        if (empty($accounts)) {
            $view->assign('accountList', []);
            return;
        }

        $session = new Zend_Session_Namespace('account-switcher');

        /** @var Account $account */
        $account = null;
        if (isset($session->accountId)) {
            // Recherche dans les comptes que l'utilisateur peut accéder
            foreach ($accounts as $accountSearch) {
                if ($accountSearch->getId() == $session->accountId) {
                    $account = $accountSearch;
                }
            }
        }
        // À défaut prend le premier
        $account = $account ?: reset($accounts);

        // Account view
        $accountView = $this->accountViewFactory->createAccountView($account, $user);

        $view->assign('accountList', $accounts);
        $view->assign('account', $accountView);
    }

    /**
     * @return User|null
     */
    public function getLoggedInUser()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            return User::load($auth->getIdentity());
        }
        return null;
    }
}
