<?php

use Classification\Domain\Axis;
use Classification\Domain\ClassificationLibrary;
use Classification\Domain\Context;
use Classification\Domain\Indicator;
use Core\Annotation\Secure;

class Classification_ContextindicatorController extends Core_Controller
{
    /**
     * @Secure("editClassificationLibrary")
     */
    public function listAction()
    {
        /** @var $library ClassificationLibrary */
        $library = ClassificationLibrary::load($this->getParam('library'));

        $this->view->listContexts = [];
        foreach ($library->getContexts() as $context) {
            $this->view->listContexts[$context->getRef()] = $context->getLabel();
        }
        $this->view->listIndicators = [];
        foreach ($library->getIndicators() as $indicator) {
            $this->view->listIndicators[$indicator->getRef()] = $indicator->getLabel();
        }
        $this->view->listAxes = array();
        foreach ($library->getAxes() as $axis) {
            $this->view->listAxes[$axis->getRef()] = $axis->getLabel();
        }

        $this->view->assign('library', $library);
        $this->setActiveMenuItemClassificationLibrary($library->getId());
    }
}
