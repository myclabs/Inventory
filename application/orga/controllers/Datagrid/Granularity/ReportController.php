<?php
/**
 * @author valentin.claras
 * @package    Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * @package    Orga
 * @subpackage Controller
 */
class Orga_Datagrid_Granularity_ReportController extends UI_Controller_Datagrid
{
    /**
     * Fonction renvoyant la liste des éléments peuplant la Datagrid.
     * @Secure("viewReport")
     */
    public function getelementsAction()
    {
        $this->request->filter->addCondition(
            DW_Model_Report::QUERY_CUBE,
            DW_Model_Cube::load($this->getParam('idCube'))
        );
        foreach (DW_Model_Report::loadList($this->request) as $report) {
            $data = array();
            $data['index'] = $report->getId();
            $data['label'] = $report->getLabel();
            $urlDetails = 'orga/granularity/report/idGranularity/'.$this->getParam('idGranularity').'/idReport/'.$data['index'];
            $data['details'] = $this->cellLink($urlDetails, __('UI', 'name', 'details'), 'share-alt');
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