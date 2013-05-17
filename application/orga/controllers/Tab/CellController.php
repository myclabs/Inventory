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
class Orga_Tab_CellController extends Core_Controller_Ajax
{
    /**
     * @var Orga_Model_Cell
     */
    protected $cell;

    /**
     * @var int
     */
    protected $idCube;

    /**
     * @see Core_Controller::init()
     */
    public function init()
    {
        parent::init();
        $this->_helper->layout()->disableLayout();
        $this->cell = Orga_Model_Cell::load(array('id' => $this->_getParam('idCell')));
        $this->idCube = $this->cell->getGranularity()->getCube()->getKey()['id'];
    }

    /**
     * Onglet des cellules enfants.
     * @Secure("viewCell")
     */
    public function childcellsAction()
    {
        $this->_forward('child', 'cell', 'orga', array('idCell' => $this->cell->getKey()['id'], 'display' => 'render'));
    }

    /**
     * Onglet des axes.
     * @Secure("viewCell")
     */
    public function axisAction()
    {
        $this->_forward('manage', 'axis', 'orga', array('idCube' => $this->idCube, 'display' => 'render'));
    }

    /**
     * Onglet des membres.
     * @Secure("viewCell")
     */
    public function memberAction()
    {
        $this->view->idCube = $this->idCube;
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
            $this->view->axes = $this->cell->getGranularity()->getCube()->getLastOrderedAxes();
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
        $this->_forward('manage', 'granularity', 'orga', array('idCube' => $this->idCube, 'display' => 'render'));
    }

    /**
     * Onglet de la pertinence des cellules enfants.
     * @Secure("viewCell")
     */
    public function relevantcellsAction()
    {
        $this->_forward('relevant', 'cell', 'orga', array('idCell' => $this->cell->getKey()['id'], 'display' => 'render'));
    }

    /**
     * Onglet de la cohérence du cube.
     * @Secure("viewCell")
     */
    public function consistencyAction()
    {
        $this->_forward('consistency', 'cube', 'orga', array('idCube' => $this->idCube, 'display' => 'render'));
    }

}