<?php

use Core\Annotation\Secure;
use Orga\Domain\Workspace;

/**
 * @author valentin.claras
 */
class Orga_TranslateController extends Core_Controller
{
    /**
     * @Secure("editWorkspace")
     */
    public function axesAction()
    {
        $this->view->assign('workspaceId', $this->getParam('workspace'));
    }

    /**
     * @Secure("editWorkspace")
     */
    public function membersAction()
    {
        $this->view->assign('workspaceId', $this->getParam('workspace'));
        $workspace = Workspace::load($this->getParam('workspace'));
        $this->view->assign('axes', $workspace->getLastOrderedAxes());
    }

    /**
     * @Secure("editWorkspace")
     */
    public function granularityReportsAction()
    {
        $this->view->assign('workspaceId', $this->getParam('workspace'));
        $workspace = Workspace::load($this->getParam('workspace'));
        $this->view->assign('granularities', $workspace->getOrderedGranularities());
    }

}