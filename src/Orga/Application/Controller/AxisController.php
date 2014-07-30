<?php

use Core\Annotation\Secure;
use Orga\Domain\Workspace;

/**
 * @author valentin.claras
 */
class Orga_AxisController extends Core_Controller
{
    /**
     * @Secure("viewWorkspace")
     */
    public function manageAction()
    {
        $workspaceId = $this->getParam('workspace');
        $this->view->assign('workspaceId', $workspaceId);
        /** @var Workspace $workspace */
        $workspace = Workspace::load($workspaceId);

        $this->view->assign('eligibleParents', $workspace->getFirstOrderedAxes());

        if ($this->hasParam('display') && ($this->getParam('display') === 'render')) {
            $this->_helper->layout()->disableLayout();
            $this->view->assign('display', false);
        } else {
            $this->view->assign('display', true);
        }
    }
}
