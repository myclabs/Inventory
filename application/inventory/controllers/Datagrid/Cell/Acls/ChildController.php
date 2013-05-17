<?php
/**
 * @author valentin.claras
 * @package Inventory
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Controlleur du Datagrid listant les Roles d'une Cellule.
 * @author valentin.claras
 * @package Inventory
 * @subpackage Controller
 */
class Inventory_Datagrid_Cell_Acls_ChildController extends UI_Controller_Datagrid
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
     * @Secure("allowCell")
     */
    function getelementsAction()
    {
        $this->request->setCustomParameters($this->request->filter->getConditions());
        $this->request->filter->setConditions(array());

        $idCell = $this->_getParam('idCell');
        $orgaCell = Orga_Model_Cell::load($idCell);
        $orgaGranularity = Orga_Model_Granularity::load(array('id' => $this->_getParam('idGranularity')));

        foreach ($orgaCell->getChildCellsForGranularity($orgaGranularity, $this->request) as $childOrgaCell) {
            $childCellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($childOrgaCell);
            $childCellDataProviderResource = User_Model_Resource_Entity::loadByEntity($childCellDataProvider);

            $data = array();
            $data['index'] = $childOrgaCell->getKey()['id'];
            foreach ($childOrgaCell->getMembers() as $member) {
                $data[$member->getAxis()->getRef()] = $member->getRef();
            }

            $listLinkedUser = array();
            $listAdministrator = array();
            foreach ($childCellDataProviderResource->getLinkedSecurityIdentities() as $linkedIdentity) {
                if ($linkedIdentity instanceof User_Model_Role) {
                    $userNumber = 0;
                    foreach ($linkedIdentity->getUsers() as $user) {
                        if ($linkedIdentity->getRef() === 'cellDataProviderAdministrator_'.$childCellDataProvider->getKey()['id']
                            || $linkedIdentity->getRef() === 'cellDataProviderContributor_'.$childCellDataProvider->getKey()['id']) {
                            $listAdministrator[] = $user->getName();
                        }
                        $userNumber ++;
                    }
                    $listLinkedUser[] = $userNumber;  //  . ' ' . $linkedIdentity->getName() . '(s)';
                }
            }

            $data['administrators'] = implode(' | ', $listAdministrator);
            $data['details'] = $this->cellPopup(
                'inventory/datagrid_cell_acls_child/list/?idCell='.$data['index'],
                implode(' | ', $listLinkedUser),
                'zoom-in'
            );

            $this->addLine($data);
        }
        $this->totalElements = $orgaCell->countTotalChildCellsForGranularity($orgaGranularity, $this->request);

        $this->send();
    }

    /**
     * Donne les détails sur un utilisateur.
     *
     * @Secure("allowCell")
     */
    public function listAction()
    {
        $this->view->idCell = $this->_getParam('idCell');
        $cellDataProviderACLResource = User_Model_Resource_Entity::loadByEntity(
            Inventory_Model_CellDataProvider::loadByOrgaCell(
                Orga_Model_Cell::load($this->view->idCell)
            )
        );

        $this->view->listRoles = array();
        foreach ($cellDataProviderACLResource->getLinkedSecurityIdentities() as $linkedIdentity) {
            if ($linkedIdentity instanceof User_Model_Role) {
                $this->view->listRoles[$linkedIdentity->getRef()] = $linkedIdentity->getName();
            }
        }
    }

}