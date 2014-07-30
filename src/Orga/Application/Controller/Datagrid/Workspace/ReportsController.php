<?php

use Core\Annotation\Secure;
use DW\Domain\Report;
use Orga\Domain\Granularity;

/**
 * @author valentin.claras
 */
class Orga_Datagrid_Workspace_ReportsController extends UI_Controller_Datagrid
{
    /**
     * @Secure("editWorkspace")
     */
    public function getelementsAction()
    {
        $granularityId = $this->getParam('granularity');
        /** @var Granularity $granularity */
        $granularity = Granularity::load($granularityId);
        $dWCube = $granularity->getDWCube();

        $this->request->filter->addCondition(Report::QUERY_CUBE, $dWCube);
        $this->request->order->addTranslatedOrder(Report::QUERY_LABEL);
        /** @var Report $report */
        foreach (Report::loadList($this->request) as $report) {
            $data = array();
            $data['index'] = $report->getId();
            $data['report'] = $this->cellTranslatedText($report->getLabel());
            $urlDetails = 'orga/granularity/view-report/granularity/'.$granularityId.'/report/'.$data['index'].'/';
            $data['link'] = $this->cellLink($urlDetails);
            $this->addline($data);
        }

        $this->totalElements = Report::countTotal($this->request);

        $this->send(true);
    }

    /**
     * @Secure("editWorkspace")
     */
    public function deleteelementAction()
    {
        Report::load($this->delete)->delete();
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

}
