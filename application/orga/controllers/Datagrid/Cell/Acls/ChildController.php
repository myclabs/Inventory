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
class Orga_Datagrid_Cell_Acls_ChildController extends UI_Controller_Datagrid
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
        $granularity = Orga_Model_Granularity::load(array('id' => $this->getParam('idGranularity')));

        foreach ($cell->getChildCellsForGranularity($granularity, $this->request) as $childCell) {
            $childCellResource = User_Model_Resource_Entity::loadByEntity($childCell);

            $data = array();
            $data['index'] = $childCell->getKey()['id'];
            foreach ($childCell->getMembers() as $member) {
                $data[$member->getAxis()->getRef()] = $member->getRef();
            }

            $listLinkedUser = array();
            $listAdministrator = array();
            foreach ($childCellResource->getLinkedSecurityIdentities() as $linkedIdentity) {
                if ($linkedIdentity instanceof User_Model_Role) {
                    $userNumber = 0;
                    foreach ($linkedIdentity->getUsers() as $user) {
                        if ($linkedIdentity->getRef() === 'cellDataProviderAdministrator_'.$childCell->getKey()['id']
                            || $linkedIdentity->getRef() === 'cellDataProviderContributor_'.$childCell->getKey()['id']) {
                            $listAdministrator[] = $user->getName();
                        }
                        $userNumber ++;
                    }
                    $listLinkedUser[] = $userNumber;  //  . ' ' . $linkedIdentity->getName() . '(s)';
                }
            }

            $data['administrators'] = implode(' | ', $listAdministrator);
            $data['details'] = $this->cellPopup(
                'orga/datagrid_cell_acls_child/list/?idCell='.$data['index'],
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
        $cellACLResource = User_Model_Resource_Entity::loadByEntity(
            Orga_Model_Cell::load($this->view->idCell)
        );

        $this->view->listRoles = array();
        foreach ($cellACLResource->getLinkedSecurityIdentities() as $linkedIdentity) {
            if ($linkedIdentity instanceof User_Model_Role) {
                $this->view->listRoles[$linkedIdentity->getRef()] = $linkedIdentity->getName();
            }
        }
    }

}