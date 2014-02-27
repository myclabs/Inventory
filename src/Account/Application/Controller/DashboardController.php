<?php

use Account\Domain\AccountRepository;
use AF\Domain\AFLibrary;
use Core\Annotation\Secure;
use Orga\ViewModel\OrganizationViewModelFactory;
use Parameter\Domain\ParameterLibrary;
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
     * @var OrganizationViewModelFactory
     */
    private $organizationVMFactory;

    /**
     * TODO faire une règle de sécurité appropriée
     * @Secure("loggedIn")
     */
    public function indexAction()
    {
        /** @var User $user */
        $user = $this->_helper->auth();

        $account = $this->accountRepository->get($this->getParam('id'));
        $this->view->assign('account', $account);

        // Organisations
        $query = new Core_Model_Query();
        $query->aclFilter->enabled = true;
        $query->aclFilter->user = $user;
        $query->aclFilter->action = Action::VIEW();
        $organizationsViewModel = array_map(function (Orga_Model_Organization $organization) use ($user) {
            return $this->organizationVMFactory->createOrganizationViewModel($organization, $user);
        }, Orga_Model_Organization::loadList($query));
        $this->view->assign('organizations', $organizationsViewModel);

        $this->view->assign('canCreateOrganization', $this->aclService->isAllowed(
            $user,
            Action::CREATE(),
            NamedResource::loadByName(Orga_Model_Organization::class)
        ));

        // Bibliothèques d'AF
        $query = new Core_Model_Query();
        $query->filter->addCondition('account', $account);
        $this->view->assign('afLibraries', AFLibrary::loadList($query));

        // Bibliothèques de paramètres
        $query = new Core_Model_Query();
        $query->filter->addCondition('account', $account);
        $this->view->assign('parameterLibraries', ParameterLibrary::loadList($query));
    }
}
