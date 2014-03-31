<?php

namespace Account\Application\Service;

use Account\Application\ViewModel\AccountView;
use Account\Application\ViewModel\AFLibraryView;
use Account\Application\ViewModel\ClassificationLibraryView;
use Account\Application\ViewModel\ParameterLibraryView;
use Account\Domain\Account;
use AF\Domain\AFLibrary;
use Classification\Domain\ClassificationLibrary;
use Core_Model_Query;
use MyCLabs\ACL\ACLManager;
use User\Domain\ACL\Actions;
use Orga_Model_Organization;
use Parameter\Domain\ParameterLibrary;
use User\Domain\User;

/**
 * Crée des représentations simplifiées de la vue d'un compte pour un utilisateur.
 *
 * @author matthieu.napoli
 */
class AccountViewFactory
{
    /**
     * @var OrganizationViewFactory
     */
    private $organizationViewFactory;

    /**
     * @var ACLManager
     */
    private $aclManager;

    public function __construct(OrganizationViewFactory $organizationViewFactory, ACLManager $aclManager)
    {
        $this->organizationViewFactory = $organizationViewFactory;
        $this->aclManager = $aclManager;
    }

    /**
     * @param Account $account
     * @param User    $user
     *
     * @return AccountView
     */
    public function createAccountView(Account $account, User $user)
    {
        $accountView = new AccountView($account->getId(), $account->getName());

        // Organisations
        $query = new Core_Model_Query();
        $query->filter->addCondition('account', $account);
        $query->aclFilter->enabled = true;
        $query->aclFilter->user = $user;
        $query->aclFilter->action = Actions::TRAVERSE;
        foreach (Orga_Model_Organization::loadList($query) as $organization) {
            /** @var Orga_Model_Organization $organization */
            $accountView->organizations[] = $this->organizationViewFactory->createOrganizationView(
                $organization,
                $user
            );
        }

        // Bibliothèques d'AF
        $query = new Core_Model_Query();
        $query->filter->addCondition('account', $account);
        foreach (AFLibrary::loadList($query) as $library) {
            /** @var AFLibrary $library */

            $libraryView = new AFLibraryView($library->getId(), $library->getLabel());
            $libraryView->canDelete = $this->aclManager->isAllowed($user, Actions::DELETE, $library);

            $accountView->afLibraries[] = $libraryView;
        }

        // Bibliothèques de paramètres
        $query = new Core_Model_Query();
        $query->filter->addCondition('account', $account);
        foreach (ParameterLibrary::loadList($query) as $library) {
            /** @var ParameterLibrary $library */

            $libraryView = new ParameterLibraryView($library->getId(), $library->getLabel());
            $libraryView->canDelete = $this->aclManager->isAllowed($user, Actions::DELETE, $library);

            $accountView->parameterLibraries[] = $libraryView;
        }

        // Bibliothèques de classification
        $query = new Core_Model_Query();
        $query->filter->addCondition('account', $account);
        foreach (ClassificationLibrary::loadList($query) as $library) {
            /** @var ClassificationLibrary $library */

            $libraryView = new ClassificationLibraryView($library->getId(), $library->getLabel());
            $libraryView->canDelete = $this->aclManager->isAllowed($user, Actions::DELETE, $library);

            $accountView->classificationLibraries[] = $libraryView;
        }

        // Est-ce que l'utilisateur peut modifier le compte
        $accountView->canEdit = $this->aclManager->isAllowed($user, Actions::EDIT, $account);

        // Est-ce que l'utilisateur peut gérer les utilisateurs
        $accountView->canAllow = $this->aclManager->isAllowed($user, Actions::ALLOW, $account);

        return $accountView;
    }
}
