<?php
/**
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Controlleur du Datagrid listant les Roles d'une Cellule.
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */
class Orga_Datagrid_Organization_Acls_ChildcellsController extends UI_Controller_Datagrid
{
    /**
     * Fonction renvoyant la liste des éléments peuplant la Datagrid.
     *
     * Récupération des paramètres de tris et filtres de la manière suivante :
     *  $this->request.
     *
     * Récupération des arguments de la manière suivante :
     *  $this->getParam('nomArgument').
     *
     * Renvoie la liste d'éléments, le nombre total et un message optionnel.
     *
     * @Secure("allowCell")
     */
    function getelementsAction()
    {
        $this->request->setCustomParameters($this->request->filter->getConditions());
        $this->request->filter->setConditions(array());

        $idCell = $this->getParam('idCell');
        $cell = Orga_Model_Cell::load($idCell);
        $granularity = Orga_Model_Granularity::load($this->getParam('idGranularity'));

        $this->request->order->addOrder(Orga_Model_Cell::QUERY_MEMBERS_HASHKEY);
        foreach ($cell->loadChildCellsForGranularity($granularity, $this->request) as $childCell) {
            $childCellResource = User_Model_Resource_Entity::loadByEntity($childCell);

            $data = array();
            $data['index'] = $childCell->getId();
            foreach ($childCell->getMembers() as $member) {
                $data[$member->getAxis()->getRef()] = $member->getRef();
            }

            $listLinkedUser = array();
            $listAdministrator = array();
            foreach ($childCellResource->getLinkedSecurityIdentities() as $linkedIdentity) {
                if ($linkedIdentity instanceof User_Model_Role) {
                    $userNumber = 0;
                    foreach ($linkedIdentity->getUsers() as $user) {
                        if ($linkedIdentity->getRef() === 'cellAdministrator_'.$childCell->getId()
                            || $linkedIdentity->getRef() === 'cellContributor_'.$childCell->getId()) {
                            $listAdministrator[] = $user->getName();
                        }
                        $userNumber ++;
                    }
                    $listLinkedUser[] = $userNumber;  //  . ' ' . $linkedIdentity->getName() . '(s)';
                }
            }

            $data['administrators'] = implode(' | ', $listAdministrator);
            $data['details'] = $this->cellPopup(
                'orga/datagrid_organization_acls_childcells/list/idCell/'.$data['index'],
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
        $idCell = $this->getParam('idCell');
        $this->view->assign('idCell', $idCell);
        /** @var Orga_Model_Cell $cell */
        $cell = Orga_Model_Cell::load($idCell);
        $this->view->assign('idGranularity', $cell->getGranularity()->getId());
        $cellACLResource = User_Model_Resource_Entity::loadByEntity($cell);

        $listRoles = array();
        foreach ($cellACLResource->getLinkedSecurityIdentities() as $linkedIdentity) {
            if ($linkedIdentity instanceof User_Model_Role) {
                $listRoles[$linkedIdentity->getRef()] = __('Orga', 'role', $linkedIdentity->getName());
            }
        }
        $this->view->assign('listRoles', $listRoles);

        $this->view->assign('labelPopup', __('Orga', 'acls', 'childCell', ['CELL' => $cell->getLabelExtended()]));
    }

}