<?php

use Core\Annotation\Secure;
use DW\Domain\Report;

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

        $this->request->filter->addCondition(Report::QUERY_CUBE, $dWCube);
        $this->request->order->addTranslatedOrder(Report::QUERY_LABEL);
        /** @var Report $report */
        foreach (Report::loadList($this->request) as $report) {
            $data = array();
            $data['index'] = $report->getId();
            $data['report'] = $this->cellTranslatedText($report->getLabel());
            $urlDetails = 'orga/granularity/view-report/idGranularity/'.$idGranularity.'/idReport/'.$data['index'].'/';
            $data['link'] = $this->cellLink($urlDetails);
            $this->addline($data);
        }

        $this->totalElements = Report::countTotal($this->request);

        $this->send(true);
    }

    /**
     * @Secure("editOrganization")
     */
    public function deleteelementAction()
    {
        Report::load($this->delete)->delete();
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

}
