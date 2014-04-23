<?php

use Account\Application\Service\AccountViewFactory;
use Account\Domain\Account;
use Account\Domain\AccountRepository;
use Core\Annotation\Secure;
use MyCLabs\ACL\ACL;
use User\Domain\ACL\Actions;
use User\Domain\User;

/**
 * @author matthieu.napoli
 */
class Account_DashboardController extends Core_Controller
{
    /**
     * @Inject
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @Inject
     * @var ACL
     */
    private $acl;

    /**
     * @Inject
     * @var AccountViewFactory
     */
    private $accountViewFactory;

    /**
     * @Secure("loggedIn")
     */
    public function indexAction()
    {
        $session = new Zend_Session_Namespace('account-switcher');

        // Un compte spécifique est demandé
        if ($this->getParam('id') !== null) {
            $session->accountId = $this->getParam('id');
            $this->redirect('account/dashboard');
            return;
        }

        /** @var User $user */
        $user = $this->_helper->auth();

        // Tous les comptes que l'utilisateur peut voir
        $accounts = $this->accountRepository->getTraversableAccounts($user);

        if (count($accounts) === 0) {
            throw new Core_Exception_User('Account', 'message', 'noAccess');
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

        $this->view->assign('accountList', $accounts);
        $this->view->assign('account', $accountView);
        $this->view->assign('canEditAccount', $this->acl->isAllowed($user, Actions::EDIT, $account));
        $this->addBreadcrumb(__('Account', 'name', 'dashboard'));
        $this->setActiveMenuItem('dashboard');
    }
}
