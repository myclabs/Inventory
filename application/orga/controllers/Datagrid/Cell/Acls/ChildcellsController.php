<?php
/**
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use User\Domain\ACL\Role\Role;
use Orga\Model\ACL\Role\CellAdminRole;
use Orga\Model\ACL\Role\CellManagerRole;
use Orga\Model\ACL\Role\CellContributorRole;
use Orga\Model\ACL\Role\CellObserverRole;

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

        $this->request->order->addOrder(Orga_Model_Cell::QUERY_TAG);
        foreach ($cell->loadChildCellsForGranularity($granularity, $this->request) as $childCell) {
            $data = [];
            $data['index'] = $childCell->getId();
            foreach ($childCell->getMembers() as $member) {
                $data[$member->getAxis()->getRef()] = $member->getCompleteRef();
            }

            $listLinkedUser = [];
            $listLinkedUser[CellAdminRole::class] = 0;
            $listLinkedUser[CellManagerRole::class] = 0;
            $listLinkedUser[CellContributorRole::class] = 0;
            $listLinkedUser[CellObserverRole::class] = 0;
            $listAdministrator = [];
            foreach ($childCell->getAllRoles() as $role) {
                $listLinkedUser[get_class($role)]++;
                if (!($role instanceof \Orga\Model\ACL\Role\CellObserverRole)) {
                    $listAdministrator[] = $role->getUser()->getName();
                }
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

        $this->view->listRoles = [
            'CellAdminRole' => CellAdminRole::getLabel(),
            'CellManagerRole' => CellManagerRole::getLabel(),
            'CellContributorRole' => CellContributorRole::getLabel(),
            'CellObserverRole' => CellObserverRole::getLabel(),
        ];
    }
}
