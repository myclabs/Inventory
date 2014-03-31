<?php

use Classification\Domain\Axis;
use Core\Annotation\Secure;

class Classification_AxisController extends Core_Controller
{
    /**
     * @Secure("viewClassification")
     */
    public function listAction()
    {
    }

    /**
     * @Secure("editClassification")
     */
    public function manageAction()
    {
        $this->view->listParents = Axis::loadList();
        $this->view->headScript()->appendFile('scripts/ui/refRefactor.js', 'text/javascript');
    }
}
