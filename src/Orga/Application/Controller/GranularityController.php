<?php

use Core\Annotation\Secure;
use DW\Application\DWViewConfiguration;
use Orga\Domain\Granularity;
use Orga\Domain\Workspace;

/**
 * @author valentin.claras
 */
class Orga_GranularityController extends Core_Controller
{
    /**
     * @Secure("editWorkspace")
     */
    public function manageAction()
    {
        $workspaceId = $this->getParam('workspace');
        $this->view->assign('workspaceId', $workspaceId);
        /** @var Workspace $workspace */
        $workspace = Workspace::load($workspaceId);

        $listAxes = [];
        foreach ($workspace->getFirstOrderedAxes() as $axis) {
            $listAxes[$axis->getRef()] = $this->translator->get($axis->getLabel());
        }
        $this->view->assign('listAxes', $listAxes);

        if ($this->hasParam('display') && ($this->getParam('display') === 'render')) {
            $this->_helper->layout()->disableLayout();
            $this->view->assign('display', false);
        } else {
            $this->view->assign('display', true);
        }
    }

    /**
     * @Secure("editWorkspaceAndCells")
     */
    public function viewReportAction()
    {
        $granularity = Granularity::load($this->getParam('granularity'));
        $workspaceId = $granularity->getWorkspace()->getId();

        $viewConfiguration = new DWViewConfiguration();
        $viewConfiguration->setComplementaryPageTitle(' <small>'.$this->translator->get($granularity->getLabel()).'</small>');
        $viewConfiguration->setOutputUrl('orga/workspace/edit/workspace/' . $workspaceId . '/tab/reports/');
        $viewConfiguration->setSaveURL('orga/granularity/view-report/granularity/' . $granularity->getId());

        if ($this->hasParam('report')) {
            $this->forward('details', 'report', 'dw',
                [
                    'viewConfiguration' => $viewConfiguration
                ]
            );
        } else {
            $this->forward('details', 'report', 'dw',
                [
                    'cube' => $granularity->getDWCube()->getId(),
                    'viewConfiguration' => $viewConfiguration
                ]
            );
        }
    }

}
