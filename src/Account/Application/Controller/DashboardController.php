<?php

use Account\Application\Service\AccountViewFactory;
use Account\Domain\Account;
use Account\Domain\AccountRepository;
use Core\Annotation\Secure;
use MyCLabs\ACL\ACLManager;
use User\Domain\ACL\Actions;
use MyCLabs\ACL\Model\ClassResource;
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
     * @var ACLManager
     */
    private $aclManager;

    /**
     * @Inject
     * @var AccountViewFactory
     */
    private $accountViewFactory;

    public function init()
    {
        $this->_helper->layout->setLayout('layout2');
    }

    /**
     * @Secure("loggedIn")
     */
    public function indexAction()
    {
        $session = new Zend_Session_Namespace(get_class());

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
        $this->view->assign('canCreateOrganization', $this->aclManager->isAllowed(
            $user,
            Actions::CREATE,
            new ClassResource(Orga_Model_Organization::class)
        ));
        $this->addBreadcrumb(__('Account', 'name', 'dashboard'));
    }
}
