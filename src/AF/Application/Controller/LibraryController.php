<?php

use AF\Domain\AFLibrary;
use Core\Annotation\Secure;

/**
 * @author matthieu.napoli
 */
class AF_LibraryController extends Core_Controller
{
    /**
     * @Secure("editAFLibrary")
     */
    public function viewAction()
    {
        /** @var $library AFLibrary */
        $library = AFLibrary::load($this->getParam('id'));

        $this->view->assign('library', $library);

        $this->view->headScript()->appendFile('scripts/ui/refRefactor.js', 'text/javascript');
    }
}
