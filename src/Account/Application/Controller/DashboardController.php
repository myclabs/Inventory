<?php

use Account\Application\Service\AccountViewFactory;
use Account\Domain\Account;
use Account\Domain\AccountRepository;
use Core\Annotation\Secure;
use User\Domain\ACL\ACLService;
use User\Domain\ACL\Action;
use User\Domain\ACL\Resource\NamedResource;
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
     * @var ACLService
     */
    private $aclService;

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
        $session = new Zend_Session_Namespace(get_class());

        // Un compte spécifique est demandé
        if ($this->getParam('id') !== null) {
            $session->accountId = $this->getParam('id');
            $this->redirect('account/dashboard');
            return;
        }

        /** @var User $user */
        $user = $this->_helper->auth();

        // TODO prendre les comptes que l'utilisateur peut voir
        $accounts = $this->accountRepository->getAll();

        // TODO tester si l'utilisateur peut voir le compte demandé
        /** @var Account $account */
        if (isset($session->accountId)) {
            $account = $this->accountRepository->get($session->accountId);
        } else {
            $account = current($accounts);
        }

        // Account view
        if (isset($session->account[$account->getId()])) {
            $accountView = $session->account[$account->getId()];
        } else {
            $accountView = $this->accountViewFactory->createAccountView($account, $user);
            $session->account[$account->getId()] = $accountView;
        }

        $this->view->assign('accountList', $accounts);
        $this->view->assign('account', $accountView);
        $this->view->assign('canCreateOrganization', $this->aclService->isAllowed(
            $user,
            Action::CREATE(),
            NamedResource::loadByName(Orga_Model_Organization::class)
        ));
    }
}
