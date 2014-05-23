<?php

use Core\Annotation\Secure;

/**
 * @author valentin.claras
 */
class Orga_Datagrid_Organization_ReportsController extends UI_Controller_Datagrid
{
    /**
     * @Secure("editOrganization")
     */
    public function getelementsAction()
    {
        $idGranularity = $this->getParam('idGranularity');
        /** @var Orga_Model_Granularity $granularity */
        $granularity = Orga_Model_Granularity::load($idGranularity);
        $dWCube = $granularity->getDWCube();

        $this->request->filter->addCondition(DW_Model_Report::QUERY_CUBE, $dWCube);
        $this->request->order->addTranslatedOrder(DW_Model_Report::QUERY_LABEL);
        /** @var DW_Model_Report $report */
        foreach (DW_Model_Report::loadList($this->request) as $report) {
            $data = array();
            $data['index'] = $report->getId();
            $data['report'] = $this->cellTranslatedText($report->getLabel());
            $urlDetails = 'orga/granularity/view-report/idGranularity/'.$idGranularity.'/idReport/'.$data['index'].'/';
            $data['link'] = $this->cellLink($urlDetails);
            $this->addline($data);
        }

        $this->totalElements = DW_Model_Report::countTotal($this->request);

        $this->send(true);
    }

    /**
     * @Secure("editOrganization")
     */
    public function deleteelementAction()
    {
        DW_Model_Report::load($this->delete)->delete();
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

}
