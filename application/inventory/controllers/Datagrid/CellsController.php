<?php
/**
 * @author valentin.claras
 * @package Inventory
 */

use Core\Annotation\Secure;


/**
 * Controller de cells
 * @package Inventory
 */
class Inventory_Datagrid_CellsController extends UI_Controller_Datagrid
{
    /**
     * Methode appelee pour remplir le tableau.
     * @Secure("viewProject")
     */
    public function getelementsAction()
    {
        $project = Inventory_Model_Project::load(array('id' => $this->_getParam('idProject')));
        /* @var User_Model_User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $listCellDataProviderResource = array();
        foreach ($connectedUser->getLinkedResources() as $cellDataProviderResource) {
            if (($cellDataProviderResource instanceof User_Model_Resource_Entity)
                && ($cellDataProviderResource->getEntity() instanceof Inventory_Model_CellDataProvider)
                && ($cellDataProviderResource->getEntity()->getProject() === $project)
                && (!in_array($cellDataProviderResource, $listCellDataProviderResource))
            ) {
                $listCellDataProviderResource[] = $cellDataProviderResource;
            }
        }
        foreach ($connectedUser->getRoles() as $userRole) {
            foreach ($userRole->getLinkedResources() as $cellDataProviderResource) {
                if (($cellDataProviderResource instanceof User_Model_Resource_Entity)
                    && ($cellDataProviderResource->getEntity() instanceof Inventory_Model_CellDataProvider)
                    && ($cellDataProviderResource->getEntity()->getProject() === $project)
                    && (!in_array($cellDataProviderResource, $listCellDataProviderResource))
                ) {
                    $listCellDataProviderResource[] = $cellDataProviderResource;
                }
            }
        }

        foreach ($listCellDataProviderResource as $cellDataProviderResource) {
            $orgaCell = $cellDataProviderResource->getEntity()->getOrgaCell();
            $data = array();
            $data['index'] = $orgaCell->getKey()['id'];
            $data['label'] = $orgaCell->getLabel();
            $data['granularity'] = $orgaCell->getGranularity()->getRef();

            $access = array();
            foreach ($cellDataProviderResource->getLinkedSecurityIdentities() as $securityIdentity) {
                if (($securityIdentity instanceof User_Model_Role) && ($connectedUser->hasRole($securityIdentity))) {
                    $access[] = explode('_', $securityIdentity->getRef())[0];
                }
            }
            $data['access'] = $this->cellList($access);

            $data['details'] = $this->cellLink('inventory/cell/details/idCell/'.$orgaCell->getKey()['id']);
            $this->addLine($data);
        }

        $this->send();
    }


}
