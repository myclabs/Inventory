<?php
/**
 * @author valentin.claras
 * @author cyril.perraud
 * @package    Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use DI\Annotation\Inject;
use User\Domain\ACL\Action;
use User\Domain\ACL\ACLService;

/**
 * @package    Orga
 * @subpackage Controller
 */
class Orga_Datagrid_Cell_ReportController extends UI_Controller_Datagrid
{
    /**
     * @Inject
     * @var ACLService
     */
    private $aclService;

    /**
     * Fonction renvoyant la liste des éléments peuplant la Datagrid.
     * @Secure("viewReport")
     */
    public function getelementsAction()
    {
        // Rapports préconfigurés
        $cube = DW_Model_Cube::load($this->getParam('idCube'));
        foreach ($cube->getReports() as $report) {
            if (! Orga_Model_GranularityReport::isDWReportCopiedFromGranularityDWReport($report)) {
                continue;
            }

            $data = array();
            $data['index'] = $report->getId();
            $data['label'] = $report->getLabel();
            $urlDetails = 'orga/tab_celldetails/report/idCell/'.$this->getParam('idCell').'/idReport/'.$data['index'];
            $data['details'] = $this->cellLink($urlDetails);
            $data['delete'] = false;

            $this->addline($data);
        }

        // Rapports personnels
        $this->request->aclFilter->enabled = true;
        $this->request->aclFilter->user = $this->_helper->auth();
        $this->request->aclFilter->action = Action::VIEW();

        $this->request->filter->addCondition(DW_Model_Report::QUERY_CUBE, $cube);
        $this->request->order->addOrder(DW_Model_Report::QUERY_LABEL);
        foreach (DW_Model_Report::loadList($this->request) as $report) {
            /** @var DW_Model_Report $report */
            $data = array();
            $data['index'] = $report->getId();
            $data['label'] = $report->getLabel();
            $urlDetails = 'orga/tab_celldetails/report/idCell/'.$this->getParam('idCell').'/idReport/'.$data['index'];
            $data['details'] = $this->cellLink($urlDetails);

            $isUserAllowedToDeleteReport = $this->aclService->isAllowed(
                $this->_helper->auth(),
                Action::DELETE(),
                $report
            );
            if (!$isUserAllowedToDeleteReport) {
                $data['delete'] = false;
            }

            $this->addline($data);
        }
        $this->send(true);
    }

    /**
     * Fonction supprimant un élément.
     * @Secure("deleteReport")
     */
    public function deleteelementAction()
    {
        DW_Model_Report::load($this->delete)->delete();
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

}