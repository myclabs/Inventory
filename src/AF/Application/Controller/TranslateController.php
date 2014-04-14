<?php

use AF\Domain\AFLibrary;
use Core\Annotation\Secure;

class AF_TranslateController extends Core_Controller
{
    /**
     * @Secure("editAFLibrary")
     */
    public function indexAction()
    {
        $library = AFLibrary::load($this->getParam('library'));

        $this->view->assign('library', $library);
        $this->setActiveMenuItemAFLibrary($library->getId());
    }
}
