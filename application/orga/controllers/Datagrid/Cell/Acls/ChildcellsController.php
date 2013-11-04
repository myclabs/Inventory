<?php
/**
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use User\Domain\ACL\Role;

/**
 * Controlleur du Datagrid listant les Roles d'une Cellule.
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */
class Orga_Datagrid_Cell_Acls_ChildcellsController extends UI_Controller_Datagrid
{
    /**
     * @Secure("allowCell")
     */
    public function getelementsAction()
    {
        $this->request->setCustomParameters($this->request->filter->getConditions());
        $this->request->filter->setConditions([]);

        $idCell = $this->getParam('idCell');
        $cell = Orga_Model_Cell::load($idCell);
        $granularity = Orga_Model_Granularity::load($this->getParam('idGranularity'));

        $this->request->order->addOrder(Orga_Model_Cell::QUERY_MEMBERS_HASHKEY);
        foreach ($cell->loadChildCellsForGranularity($granularity, $this->request) as $childCell) {
            $data = [];
            $data['index'] = $childCell->getId();
            foreach ($childCell->getMembers() as $member) {
                $data[$member->getAxis()->getRef()] = $member->getRef();
            }

            $listLinkedUser = [];
            $listAdministrator = [];
            foreach ($cell->getAllRoles() as $role) {
                $listAdministrator[] = $role->getUser()->getName();
                $listLinkedUser[] = 1;  //  . ' ' . $linkedIdentity->getName() . '(s)';
            }

            $data['administrators'] = implode(' | ', $listAdministrator);
            $data['details'] = $this->cellPopup(
                'orga/datagrid_cell_acls_childcells/list/idCell/'.$data['index'],
                implode(' | ', $listLinkedUser),
                'zoom-in'
            );

            $this->addLine($data);
        }
        $this->totalElements = $cell->countTotalChildCellsForGranularity($granularity, $this->request);

        $this->send();
    }

    /**
     * Donne les détails sur un utilisateur.
     *
     * @Secure("allowCell")
     */
    public function listAction()
    {
        $this->view->idCell = $this->getParam('idCell');
        $cell = Orga_Model_Cell::load($this->view->idCell);

        $this->view->listRoles = [];
        foreach ($cell->getAllRoles() as $role) {
            $this->view->listRoles[] = __('Orga', 'role', $role->getLabel());
        }
    }
}
