<?php

use Core\Annotation\Secure;
use DI\Annotation\Inject;

/**
 * @author valentin.claras
 */
class Orga_Datagrid_Organization_ConsistencyController extends UI_Controller_Datagrid
{
    /**
     * @Inject
     * @var Orga_OrganizationConsistency
     */
    private $organizationConsistency;

    /**
     * @Secure("editOrganization")
     */
    public function getelementsAction()
    {
        /** @var Orga_Model_Organization $organization */
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));
        $consistency = $this->organizationConsistency->check($organization);

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

        $data['index'] = 4;
        $data['diagnostic'] = $consistency['okCrossedGranularities'];
        $data['control'] = $consistency['controlCrossedGranularities'];
        $data['failure'] = $this->cellText($consistency['failureCrossedGranularities']);
        $this->addLine($data);

        $this->send();
    }

}