<?php
/**
 * @author valentin.claras
 * @package    DW
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Classe du controler de Data Warehouse
 * @package DW
 */
class DW_CubeController extends Core_Controller_Ajax
{

    /**
     * Stream l'export excel d'un report.
     * @Secure("viewDWCube")
     */
    public function exportAction()
    {
        set_time_limit(60);
        $export = new DW_Export_Indicators(DW_Model_Cube::load($this->getParam('idCube')));
        $export->display();
    }

}