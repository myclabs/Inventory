<?php

use Account\Domain\Account;
use Account\Domain\AccountRepository;
use AF\Domain\AFLibrary;
use Core\Annotation\Secure;

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

        $this->view->headScript()->appendFile('scripts/ui/refRefactor.js', 'text/javascript');
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
}
