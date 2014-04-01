<?php

use Account\Domain\Account;
use Account\Domain\AccountRepository;
use Classification\Domain\ClassificationLibrary;
use Core\Annotation\Secure;
use MyCLabs\ACL\ACLManager;
use User\Domain\ACL\Actions;

/**
 * @author matthieu.napoli
 */
class Classification_LibraryController extends Core_Controller
{
    /**
     * @Inject
     * @var ACLManager
     */
    private $aclManager;

    /**
     * @Inject
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @Secure("viewClassificationLibrary")
     */
    public function viewAction()
    {
        /** @var $library ClassificationLibrary */
        $library = ClassificationLibrary::load($this->getParam('id'));

        $this->view->assign('library', $library);
        $canEdit = $this->aclManager->isAllowed($this->_helper->auth(), Actions::EDIT, $library);
        $this->view->assign('edit', $canEdit);
        $this->setActiveMenuItemClassificationLibrary($library->getId());
    }

    /**
     * @Secure("editAccount")
     */
    public function newAction()
    {
        /** @var $account Account */
        $account = $this->accountRepository->get($this->getParam('account'));

        if ($this->getRequest()->isPost()) {
            $label = trim($this->getParam('label'));

            if ($label == '') {
                UI_Message::addMessageStatic(__('UI', 'formValidation', 'allFieldsRequired'));
            } else {
                $library = new ClassificationLibrary($account, $label);
                $library->save();
                $this->entityManager->flush();

                UI_Message::addMessageStatic(
                    __('Classification', 'library', 'libraryCreated'),
                    UI_Message::TYPE_SUCCESS
                );
                $this->redirect('classification/library/view/id/' . $library->getId());
                return;
            }
        }

        $this->view->assign('account', $account);
    }

    /**
     * @Secure("deleteClassificationLibrary")
     */
    public function deleteAction()
    {
        /** @var $library ClassificationLibrary */
        $library = ClassificationLibrary::load($this->getParam('id'));

        $library->delete();
        try {
            $this->entityManager->flush();
            UI_Message::addMessageStatic(__('UI', 'message', 'deleted'), UI_Message::TYPE_SUCCESS);
        } catch (Core_ORM_ForeignKeyViolationException $e) {
            UI_Message::addMessageStatic(
                __('Classification', 'library', 'libraryDeletionError'),
                UI_Message::TYPE_ERROR
            );
        }

        $this->redirect('account/dashboard');
    }
}
