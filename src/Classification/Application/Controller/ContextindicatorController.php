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
        foreach ($library->getContexts()->toArray() as $context) {
            $this->view->listContexts[$context->getId()] = $this->translator->toString($context->getLabel());
        }
        $this->view->listIndicators = [];
        foreach ($library->getIndicators()->toArray() as $indicator) {
            $this->view->listIndicators[$indicator->getId()] = $this->translator->toString($indicator->getLabel());
        }
        $this->view->listAxes = array();
        foreach ($library->getAxes()->toArray() as $axis) {
            $this->view->listAxes[$axis->getId()] = $this->translator->toString($axis->getLabel());
        }

        $this->view->assign('library', $library);
        $this->setActiveMenuItemClassificationLibrary($library->getId());
    }
}
