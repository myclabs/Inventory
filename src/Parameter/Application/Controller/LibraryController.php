<?php

use Account\Domain\Account;
use Account\Domain\AccountRepository;
use Core\Annotation\Secure;
use MyCLabs\ACL\ACL;
use Parameter\Domain\ParameterLibrary;
use User\Domain\ACL\Actions;

/**
 * @author matthieu.napoli
 */
class Parameter_LibraryController extends Core_Controller
{
    /**
     * @Inject
     * @var ACL
     */
    private $acl;

    /**
     * @Inject
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @Secure("viewParameterLibrary")
     */
    public function viewAction()
    {
        /** @var $library ParameterLibrary */
        $library = ParameterLibrary::load($this->getParam('id'));

        $this->view->assign('library', $library);
        $canEdit = $this->acl->isAllowed($this->_helper->auth(), Actions::EDIT, $library);
        $this->view->assign('edit', $canEdit);
        $this->setActiveMenuItemParameterLibrary($library->getId());
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
                $library = new ParameterLibrary($account, $label);
                $library->save();
                $this->entityManager->flush();

                UI_Message::addMessageStatic(__('Parameter', 'library', 'libraryCreated'), UI_Message::TYPE_SUCCESS);
                $this->redirect('parameter/library/view/id/' . $library->getId());
                return;
            }
        }

        $this->view->assign('account', $account);
    }

    /**
     * @Secure("deleteParameterLibrary")
     */
    public function deleteAction()
    {
        /** @var $library ParameterLibrary */
        $library = ParameterLibrary::load($this->getParam('id'));

        $library->delete();
        try {
            $this->entityManager->flush();
            UI_Message::addMessageStatic(__('UI', 'message', 'deleted'), UI_Message::TYPE_SUCCESS);
        } catch (Core_ORM_ForeignKeyViolationException $e) {
            UI_Message::addMessageStatic(__('Parameter', 'library', 'libraryDeletionError'), UI_Message::TYPE_ERROR);
        }

        $this->redirect('account/dashboard');
    }
}
