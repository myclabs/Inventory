<?php

use Account\Domain\Account;
use Account\Domain\AccountRepository;
use AF\Domain\AFLibrary;
use Core\Annotation\Secure;
use Core\Translation\TranslatedString;

/**
 * @author matthieu.napoli
 */
class AF_LibraryController extends Core_Controller
{
    /**
     * @Inject
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @Secure("editAFLibrary")
     */
    public function viewAction()
    {
        /** @var $library AFLibrary */
        $library = AFLibrary::load($this->getParam('id'));

        $this->view->assign('library', $library);

        $this->setActiveMenuItemAFLibrary($library->getId());
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
                $label = $this->translator->set(new TranslatedString(), $label);
                $library = new AFLibrary($account, $label);
                $library->save();
                $this->entityManager->flush();

                UI_Message::addMessageStatic(__('AF', 'library', 'libraryCreated'), UI_Message::TYPE_SUCCESS);
                $this->redirect('af/library/view/id/' . $library->getId());
                return;
            }
        }

        $this->view->assign('account', $account);
    }

    /**
     * @Secure("deleteAFLibrary")
     */
    public function deleteAction()
    {
        /** @var $library AFLibrary */
        $library = AFLibrary::load($this->getParam('id'));

        $library->delete();
        try {
            $this->entityManager->flush();
            UI_Message::addMessageStatic(__('UI', 'message', 'deleted'), UI_Message::TYPE_SUCCESS);
        } catch (Core_ORM_ForeignKeyViolationException $e) {
            UI_Message::addMessageStatic(__('AF', 'library', 'libraryDeletionError'), UI_Message::TYPE_ERROR);
        }

        $this->redirect('account/dashboard');
    }
}
