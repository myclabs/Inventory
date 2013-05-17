<?php
/**
 * @author valentin.claras
 * @package Inventory
 */

use Core\Annotation\Secure;

/**
 * @author valentin.claras
 * @package Inventory
 */
class Inventory_GranularityController extends Core_Controller
{
    /**
     * Affiche le Report de DW d'un GranularityDataProvider.
     * @Secure("viewReport")
     */
    public function reportAction()
    {
        $orgaGranularity = Orga_Model_Granularity::load(array('id' => $this->_getParam('idGranularity')));

        $viewConfiguration = new DW_ViewConfiguration();
        $viewConfiguration->setComplementaryPageTitle(' <small>'.$orgaGranularity->getLabel().'</small>');
        $viewConfiguration->setOutputURL('inventory/cell/details?idCell='.$this->_getParam('idCell').'&tab=configuration');
        $viewConfiguration->setSaveURL('inventory/granularity/report?idGranularity='.$orgaGranularity->getKey()['id'].'&idCell='.$this->_getParam('idCell').'&');
        if ($this->_hasParam('idReport')) {
            $this->_forward('details', 'report', 'dw', array(
                'idReport' => $this->_getParam('idReport'),
                'viewConfiguration' => $viewConfiguration
            ));
        } else {
            $this->_forward('details', 'report', 'dw', array(
                'idCube' => $this->_getParam('idCube'),
                'viewConfiguration' => $viewConfiguration
            ));
        }
    }

}