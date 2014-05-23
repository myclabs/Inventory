<?php

use Classification\Domain\ClassificationLibrary;
use Core\Annotation\Secure;

class Classification_TranslateController extends Core_Controller
{
    /**
     * @Secure("editClassificationLibrary")
     */
    public function indexAction()
    {
        $library = ClassificationLibrary::load($this->getParam('library'));

        $this->view->assign('library', $library);
        $this->setActiveMenuItemClassificationLibrary($library->getId());
    }
}
