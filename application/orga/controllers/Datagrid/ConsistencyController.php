<?php
/**
 * Classe Orga_Datagrid_ConsistencyController
 * @author valentin.claras
 * @author diana.dragusin
 * @package Orga
 */

use Core\Annotation\Secure;

/**
 * Controller du datagrid de coherence
 * @package Orga
 */
class Orga_Datagrid_ConsistencyController extends UI_Controller_Datagrid
{

    /**
     * Fonction renvoyant la liste des éléments peuplant la Datagrid.
     *
     * Récupération des paramètres de tris et filtres de la manière suivante :
     *  $this->request.
     *
     * Récupération des arguments de la manière suivante :
     *  $this->_getParam('nomArgument').
     *
     * Renvoie la liste d'éléments, le nombre total et un message optionnel.
     *
     * @Secure("viewOrgaCube")
     */
    public function getelementsAction()
    {
        $cube = Orga_Model_Cube::load(array('id' => $this->_getParam('idCube')));
        $consistency = Orga_CubeConsistency::getInstance()->check($cube);

        $data['index'] = 1;
        $data['diagnostic'] = $consistency['okAxis'];
        $data['control'] = $consistency['controlAxis'];
        $data['failure'] = $this->cellText($consistency['failureAxis']);
        $this->addLine($data);

        $data['index'] = 2;
        $data['diagnostic'] = $consistency['okMemberParents'];
        $data['control'] = $consistency['controlMemberParents'];
        $data['failure'] = $this->cellText($consistency['failureMemberParents']);
        $this->addLine($data);

        $data['index'] = 3;
        $data['diagnostic'] = $consistency['okMemberChildren'];
        $data['control'] = $consistency['controlMemberChildren'];
        $data['failure'] = $this->cellText($consistency['failureMemberChildren']);
        $this->addLine($data);

        $this->send();
    }

}