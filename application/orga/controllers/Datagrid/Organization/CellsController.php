<?php
/**
 * @author valentin.claras
 * @package Orga
 */

use Core\Annotation\Secure;
use Orga\Model\ACL\Role\AbstractCellRole;
use User\Domain\ACL\Action;
use User\Domain\ACL\Role\Role;
use User\Domain\User;

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
        /* @var User $connectedUser */
        $connectedUser = $this->_helper->auth();

        /** @var Orga_Model_Cell[] $cells */
        $cells = [];
        $roles = [];
        foreach ($connectedUser->getRoles() as $role) {
            if ($role instanceof AbstractCellRole) {
                $cell = $role->getCell();
                if ($cell->getGranularity()->getOrganization() === $organization) {
                    if (! in_array($cell, $cells)) {
                        $cells[] = $cell;
                        $roles[$cell->getId()] = [$role];
                    } else {
                        $roles[$cell->getId()][] = $role;
                    }
                }
            }
        }

        foreach ($cells as $cell) {
            $data = [];
            $data['index'] = $cell->getId();
            $data['label'] = $cell->getLabel();

            $access = [];
            foreach ($roles[$cell->getId()] as $role) {
                /** @var \User\Domain\ACL\Role\Role $role */
                $access[] = $role->getLabel();
            }
            $data['access'] = $this->cellList($access);

            $data['details'] = $this->cellLink('orga/cell/details/idCell/'.$cell->getId(), __('Orga', 'home', 'dataInputLink'), 'share-alt');
            $this->addLine($data);
        }

        $this->send();
    }


}
