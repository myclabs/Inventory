<?php

use Core\Annotation\Secure;
use DI\Annotation\Inject;
use Orga\Domain\Service\WorkspaceConsistencyService;
use Orga\Domain\Workspace;

/**
 * @author valentin.claras
 */
class Orga_Datagrid_Workspace_ConsistencyController extends UI_Controller_Datagrid
{
    /**
     * @Inject
     * @var WorkspaceConsistencyService
     */
    private $workspaceService;

    /**
     * @Secure("editWorkspace")
     */
    public function getelementsAction()
    {
        /** @var Workspace $workspace */
        $workspace = Workspace::load($this->getParam('workspace'));
        $consistency = $this->workspaceService->check($workspace);

        $data['index'] = 1;
        $data['diagnostic'] = $consistency['okAxis'];
        $data['control'] = $consistency['controlAxis'];
        $data['failure'] = $this->cellText($consistency['failureAxis']);
        $this->addLine($data);

        $data['index'] = 2;
        $data['diagnostic'] = $consistency['okMemberParents'];
        $data['control'] = $consistency['controlMemberParents'];
        $data['failure'] = $this->cellText($consistency['failureMemberParents']);
        $this->addLine($data);

        $data['index'] = 3;
        $data['diagnostic'] = $consistency['okMemberChildren'];
        $data['control'] = $consistency['controlMemberChildren'];
        $data['failure'] = $this->cellText($consistency['failureMemberChildren']);
        $this->addLine($data);

        $this->send();
    }

}