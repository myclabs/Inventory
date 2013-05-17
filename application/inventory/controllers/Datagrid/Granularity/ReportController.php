<?php
/**
 * @author valentin.claras
 * @package    Inventory
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * @package    Inventory
 * @subpackage Controller
 */
class Inventory_Datagrid_Granularity_ReportController extends UI_Controller_Datagrid
{
    /**
     * Fonction renvoyant la liste des éléments peuplant la Datagrid.
     * @Secure("viewReport")
     */
    public function getelementsAction()
    {
        $this->request->filter->addCondition(DW_Model_Report::QUERY_CUBE, $this->_getParam('idCube'));
        foreach (DW_Model_Report::loadList($this->request) as $report) {
            $data = array();
            $data['index'] = $report->getKey()['id'];
            $data['label'] = $report->getLabel();
            $urlDetails = 'inventory/granularity/report?idCell='.$this->_getParam('idCell').
                '&idGranularity='.$this->_getParam('idGranularity').'&idReport='.$data['index'];
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
        DW_Model_Report::load(array('id' => $this->delete))->delete();
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

}