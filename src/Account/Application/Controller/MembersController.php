<?php

use Account\Application\Service\AccountRoleManager;
use Account\Domain\Account;
use Account\Domain\AccountRepository;
use Account\Domain\ACL\AccountAdminRole;
use Core\Annotation\Secure;
use Core\Work\ServiceCall\ServiceCallTask;
use MyCLabs\ACL\Model\Role;
use MyCLabs\Work\Dispatcher\WorkDispatcher;

/**
 * @author matthieu.napoli
 */
class Account_MembersController extends Core_Controller
{
    /**
     * @Inject
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @Inject
     * @var WorkDispatcher
     */
    private $workDispatcher;

    /**
     * @Inject("work.waitDelay")
     * @var int
     */
    private $waitDelay;

    /**
     * @Secure("allowAccount")
     */
    public function indexAction()
    {
        /** @var Account $account */
        $account = $this->accountRepository->get($this->getParam('account'));

        $this->view->assign('account', $account);
        $this->view->assign('adminRoles', $account->getAdminRoles());

        $this->setActiveMenuItem('members');
    }

    /**
     * @Secure("allowAccount")
     */
    public function addAdminAction()
    {
        /** @var Account $account */
        $account = $this->accountRepository->get($this->getParam('account'));

        if ($this->getRequest()->isPost()) {
            $email = trim($this->getParam('email'));

            if (empty($email)) {
                UI_Message::addMessageStatic(__('UI', 'formValidation', 'invalidEmail'));
                $this->redirect('account/members?account=' . $account->getId());
            }

            $task = new ServiceCallTask(
                AccountRoleManager::class,
                'addAdminRole',
                [$account->getId(), $email],
                __('Account', 'role', 'task.addRole', [
                    'ROLE' => __('Account', 'role', 'accountAdmin'),
                    'USER' => $email,
                ])
            );
            $success = function () {
                UI_Message::addMessageStatic(__('UI', 'message', 'added'), UI_Message::TYPE_SUCCESS);
            };
            $timeout = function () {
                UI_Message::addMessageStatic(__('UI', 'message', 'addedLater'), UI_Message::TYPE_SUCCESS);
            };
            $error = function (Exception $e) {
                throw $e;
            };
            $this->workDispatcher->runBackground($task, $this->waitDelay, $success, $timeout, $error);
        }

        $this->redirect('account/members?account=' . $account->getId());
    }

    /**
     * @Secure("allowAccount")
     */
    public function removeAdminAction()
    {
        /** @var Account $account */
        $account = $this->accountRepository->get($this->getParam('account'));

        if ($this->getRequest()->isPost()) {
            $roleId = $this->getParam('role');
            /** @var Role $role */
            $role = $this->entityManager->find(Role::class, $roleId);

            $task = new ServiceCallTask(
                AccountRoleManager::class,
                'removeRole',
                [$roleId],
                __('Account', 'role', 'task.removeRole', [
                    'ROLE' => __('Account', 'role', 'accountAdmin'),
                    'USER' => $role->getSecurityIdentity()->getEmail(),
                ])
            );
            $success = function () {
                UI_Message::addMessageStatic(__('UI', 'message', 'deleted'), UI_Message::TYPE_SUCCESS);
            };
            $timeout = function () {
                UI_Message::addMessageStatic(__('UI', 'message', 'deletedLater'), UI_Message::TYPE_SUCCESS);
            };
            $error = function (Exception $e) {
                throw $e;
            };
            $this->workDispatcher->runBackground($task, $this->waitDelay, $success, $timeout, $error);
        }

        $this->redirect('account/members?account=' . $account->getId());
    }
}
