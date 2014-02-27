<?php

namespace Account\Application\Service;

use Account\Application\ViewModel\AccountView;
use Account\Application\ViewModel\AFLibraryView;
use Account\Application\ViewModel\ParameterLibraryView;
use Account\Domain\Account;
use AF\Domain\AFLibrary;
use Core_Model_Query;
use Orga_Model_Organization;
use Parameter\Domain\ParameterLibrary;
use User\Domain\ACL\Action;
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

    public function __construct(OrganizationViewFactory $organizationViewFactory)
    {
        $this->organizationViewFactory = $organizationViewFactory;
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

        // TODO améliorer : Organisations
        $query = new Core_Model_Query();
        $query->aclFilter->enabled = true;
        $query->aclFilter->user = $user;
        $query->aclFilter->action = Action::VIEW();
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
            $accountView->afLibraries[] = new AFLibraryView($library->getId(), $library->getLabel());
        }

        // Bibliothèques de paramètres
        $query = new Core_Model_Query();
        $query->filter->addCondition('account', $account);
        foreach (ParameterLibrary::loadList($query) as $library) {
            /** @var ParameterLibrary $library */
            $accountView->parameterLibraries[] = new ParameterLibraryView($library->getId(), $library->getLabel());
        }

        // TODO Bibliothèques d'indicateurs

        return $accountView;
    }
}
