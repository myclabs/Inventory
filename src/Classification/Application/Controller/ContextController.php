<?php

use Classification\Domain\ClassificationLibrary;
use Core\Annotation\Secure;

class Classification_ContextController extends Core_Controller
{
    /**
     * @Secure("editClassificationLibrary")
     */
    public function listAction()
    {
        /** @var ClassificationLibrary $library */
        $library = ClassificationLibrary::load($this->getParam('library'));

        $this->view->assign('library', $library);
        $this->view->headScript()->appendFile('scripts/ui/refRefactor.js', 'text/javascript');
        $this->setActiveMenuItemClassificationLibrary($library->getId());
    }
}
