<?php

use Classification\Domain\ClassificationLibrary;
use Core\Annotation\Secure;

class Classification_AxisController extends Core_Controller
{
    /**
     * @Secure("editClassificationLibrary")
     */
    public function listAction()
    {
        /** @var ClassificationLibrary $library */
        $library = ClassificationLibrary::load($this->getParam('library'));

        $this->view->assign('library', $library);
        $this->view->assign('listParents', $library->getRootAxes());
        $this->setActiveMenuItemClassificationLibrary($library->getId());
    }
}
