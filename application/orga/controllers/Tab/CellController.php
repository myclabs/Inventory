<?php
/**
 * @author valentin.claras
 * @author simon.rieu
 * @package Orga
 */

use Core\Annotation\Secure;

/**
 * Classe de gestion des onglets
 * @package Orga
 */
class Orga_Tab_CellController extends Core_Controller
{
    /**
     * @var Orga_Model_Cell
     */
    protected $cell;

    /**
     * @var int
     */
    protected $idProject;

    /**
     * @see Core_Controller::init()
     */
    public function init()
    {
        parent::init();
        $this->_helper->layout()->disableLayout();
        $this->cell = Orga_Model_Cell::load(array('id' => $this->getParam('idCell')));
        $this->idProject = $this->cell->getGranularity()->getProject()->getKey()['id'];
    }

    /**
     * Onglet des cellules enfants.
     * @Secure("viewCell")
     */
    public function childcellsAction()
    {
        $this->forward('child', 'cell', 'orga', array('idCell' => $this->cell->getKey()['id'], 'display' => 'render'));
    }

    /**
     * Onglet des axes.
     * @Secure("viewCell")
     */
    public function axisAction()
    {
        $this->forward('manage', 'axis', 'orga', array('idProject' => $this->idProject, 'display' => 'render'));
    }

    /**
     * Onglet des membres.
     * @Secure("viewCell")
     */
    public function memberAction()
    {
        $this->view->idProject = $this->idProject;
        if ($this->cell->getGranularity()->hasAxes()) {
            $axes = array();
            $idAxes = array();
            foreach ($this->cell->getMembers() as $members) {
                $axis = $members->getAxis()->getDirectNarrower();
                while ($axis !== null) {
                    if (!(in_array($axis->getKey()['id'], $idAxes))) {
                        $axes[] = $axis;
                        $idAxes[] = $axis->getKey()['id'];
                    }
                    $axis = $axis->getDirectNarrower();
                }
            }
            $this->view->axes = $axes;
            $this->view->idFilterCell = $this->cell->getKey()['id'];
            $this->view->ambiantGranularity = $this->cell->getGranularity();
        } else {
            $this->view->axes = $this->cell->getGranularity()->getProject()->getLastOrderedAxes();
            $this->view->idFilterCell = null;
            $this->view->ambiantGranularity = null;
        }

        $this->view->display = false;

        $this->_helper->viewRenderer('member/manage', 'member', true);
    }

    /**
     * Onglet des granularités.
     * @Secure("viewCell")
     */
    public function granularityAction()
    {
        $this->forward('manage', 'granularity', 'orga', array('idProject' => $this->idProject, 'display' => 'render'));
    }

    /**
     * Onglet de la pertinence des cellules enfants.
     * @Secure("viewCell")
     */
    public function relevantcellsAction()
    {
        $this->forward('relevant', 'cell', 'orga', array('idCell' => $this->cell->getKey()['id'], 'display' => 'render'));
    }

    /**
     * Onglet de la cohérence du project.
     * @Secure("viewCell")
     */
    public function consistencyAction()
    {
        $this->forward('consistency', 'project', 'orga', array('idProject' => $this->idProject, 'display' => 'render'));
    }

}