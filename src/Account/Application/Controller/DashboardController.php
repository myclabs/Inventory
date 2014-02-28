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
     * TODO tester si l'utilisateur peut voir le compte demandé
     * @Secure("loggedIn")
     */
    public function indexAction()
    {
        /** @var User $user */
        $user = $this->_helper->auth();

        $this->view->assign('accountList', $this->accountRepository->getAll());

        /** @var Account $account */
        $account = $this->accountRepository->get($this->getParam('id'));

        $this->view->assign('account', $this->accountViewFactory->createAccountView($account, $user));

        $this->view->assign('canCreateOrganization', $this->aclService->isAllowed(
            $user,
            Action::CREATE(),
            NamedResource::loadByName(Orga_Model_Organization::class)
        ));
    }
}
