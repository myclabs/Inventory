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
use Mnapoli\Translated\Translator;
use MyCLabs\ACL\ACL;
use User\Domain\ACL\Actions;
use Orga\Domain\Workspace;
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
     * @var WorkspaceViewFactory
     */
    private $workspaceViewFactory;

    /**
     * @var ACL
     */
    private $acl;

    /**
     * @var Translator
     */
    private $translator;

    public function __construct(
        WorkspaceViewFactory $workspaceViewFactory,
        ACL $acl,
        Translator $translator
    ) {
        $this->workspaceViewFactory = $workspaceViewFactory;
        $this->acl = $acl;
        $this->translator = $translator;
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
        $query->aclFilter->enable($user, Actions::TRAVERSE);
        foreach (Workspace::loadList($query) as $workspace) {
            /** @var Workspace $workspace */
            $accountView->workspaces[] = $this->workspaceViewFactory->createWorkspaceView(
                $workspace,
                $user
            );
        }

        // Bibliothèques d'AF
        $query = new Core_Model_Query();
        $query->filter->addCondition('account', $account);
        $query->aclFilter->enable($user, Actions::VIEW);
        foreach (AFLibrary::loadList($query) as $library) {
            /** @var AFLibrary $library */

            $libraryView = new AFLibraryView(
                $library->getId(),
                $this->translator->get($library->getLabel())
            );
            $libraryView->canDelete = $this->acl->isAllowed($user, Actions::DELETE, $library);

            $accountView->afLibraries[] = $libraryView;
        }

        // Bibliothèques de paramètres
        $query = new Core_Model_Query();
        $query->filter->addCondition('account', $account);
        $query->aclFilter->enable($user, Actions::VIEW);
        foreach (ParameterLibrary::loadList($query) as $library) {
            /** @var ParameterLibrary $library */

            $libraryView = new ParameterLibraryView(
                $library->getId(),
                $this->translator->get($library->getLabel())
            );
            $libraryView->canDelete = $this->acl->isAllowed($user, Actions::DELETE, $library);

            $accountView->parameterLibraries[] = $libraryView;
        }

        // Bibliothèques de classification
        $query = new Core_Model_Query();
        $query->filter->addCondition('account', $account);
        $query->aclFilter->enable($user, Actions::VIEW);
        foreach (ClassificationLibrary::loadList($query) as $library) {
            /** @var ClassificationLibrary $library */

            $libraryView = new ClassificationLibraryView(
                $library->getId(),
                $this->translator->get($library->getLabel())
            );
            $libraryView->canDelete = $this->acl->isAllowed($user, Actions::DELETE, $library);

            $accountView->classificationLibraries[] = $libraryView;
        }

        // Est-ce que l'utilisateur peut modifier le compte
        $accountView->canEdit = $this->acl->isAllowed($user, Actions::EDIT, $account);

        // Est-ce que l'utilisateur peut gérer les utilisateurs
        $accountView->canAllow = $this->acl->isAllowed($user, Actions::ALLOW, $account);

        return $accountView;
    }
}
