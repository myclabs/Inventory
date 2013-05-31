<?php
/**
 * @author valentin.claras
 * @author cyril.perraud
 * @package    Simulation
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * @package    Simulation
 * @subpackage Controller
 */
class Simulation_Datagrid_ReportController extends UI_Controller_Datagrid
{
    /**
     * Fonction renvoyant la liste des éléments peuplant la Datagrid.
     *
     * @Secure("loggedIn")
     */
    public function getelementsAction()
    {
        $this->request->filter->addCondition(DW_Model_Report::QUERY_CUBE, $this->getParam('idCube'));
        $this->request->order->addOrder(DW_Model_Report::QUERY_LABEL);
        foreach (DW_Model_Report::loadList($this->request) as $report) {
            $data = array();
            $data['index'] = $report->getKey()['id'];
            $data['label'] = $report->getLabel();
            $urlDetails = 'simulation/set/report?idSet='.$this->getParam('idSet').'&idReport='.$data['index'];
            $data['details'] = $this->cellLink($urlDetails, __('UI', 'name', 'details'), 'share-alt');
            $this->addline($data);
        }
        $this->send(true);
    }

    /**
     * Fonction supprimant un élément.
     *
     * @Secure("loggedIn")
     */
    public function deleteelementAction()
    {
        DW_Model_Report::load(array('id' => $this->delete))->delete();
        $this->message = __('UI', 'messages', 'deleted');
        $this->send();
    }

}