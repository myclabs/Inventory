<?php
/**
 * @author valentin.claras
 * @package Orga
 */

use Core\Annotation\Secure;


/**
 * Controller de cells
 * @package Orga
 */
class Orga_Datagrid_Organization_CellsController extends UI_Controller_Datagrid
{
    /**
     * Methode appelee pour remplir le tableau.
     * @Secure("viewOrganization")
     */
    public function getelementsAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));
        /* @var User_Model_User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $listCellResource = array();
        foreach ($connectedUser->getLinkedResources() as $cellResource) {
            if (($cellResource instanceof User_Model_Resource_Entity)
                && ($cellResource->getEntity() instanceof Orga_Model_Cell)
                && ($cellResource->getEntity()->getGranularity()->getOrganization() === $organization)
                && (!in_array($cellResource, $listCellResource))
            ) {
                $listCellResource[] = $cellResource;
            }
        }
        foreach ($connectedUser->getRoles() as $userRole) {
            foreach ($userRole->getLinkedResources() as $cellResource) {
                if (($cellResource instanceof User_Model_Resource_Entity)
                    && ($cellResource->getEntity() instanceof Orga_Model_Cell)
                    && ($cellResource->getEntity()->getGranularity()->getOrganization() === $organization)
                    && (!in_array($cellResource, $listCellResource))
                ) {
                    $listCellResource[] = $cellResource;
                }
            }
        }

        foreach ($listCellResource as $cellResource) {
            $cell = $cellResource->getEntity();
            $data = array();
            $data['index'] = $cell->getId();
            $data['label'] = $cell->getLabel();

            $access = array();
            foreach ($cellResource->getLinkedSecurityIdentities() as $securityIdentity) {
                if (($securityIdentity instanceof User_Model_Role) && ($connectedUser->hasRole($securityIdentity))) {
                    $access[] = $securityIdentity->getName();
                }
            }
            $data['access'] = $this->cellList($access);

            $data['details'] = $this->cellLink('orga/cell/details/idCell/'.$cell->getId());
            $this->addLine($data);
        }

        $this->send();
    }


}
