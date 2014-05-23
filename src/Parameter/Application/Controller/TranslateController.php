<?php

use Core\Annotation\Secure;
use Parameter\Domain\ParameterLibrary;

/**
 * @author valentin.claras
 */
class Parameter_TranslateController extends Core_Controller
{
    /**
     * @Secure("editParameterLibrary")
     */
    public function indexAction()
    {
        $library = ParameterLibrary::load($this->getParam('library'));

        $this->view->assign('library', $library);
        $this->setActiveMenuItemParameterLibrary($library->getId());
    }
}
