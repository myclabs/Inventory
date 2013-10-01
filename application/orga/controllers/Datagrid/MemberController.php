<?php
/**
 * Classe Orga_Datagrid_MemberController
 * @author valentin.claras
 * @author cyril.perraud
 * @package Orga
 */

use Core\Annotation\Secure;
use DI\Annotation\Inject;

/**
 * Enter description here ...
 * @package Orga
 */
class Orga_Datagrid_MemberController extends UI_Controller_Datagrid
{

    /**
     * @Inject
     * @var Core_Work_Dispatcher
     */
    private $workDispatcher;

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
     * @Secure("viewMembers")
     */
    public function getelementsAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));
        $axis = Orga_Model_Axis::loadByRefAndOrganization($this->getParam('refAxis'), $organization);

        $this->request->filter->addCondition(Orga_Model_Member::QUERY_AXIS, $axis);
        $this->request->order->addOrder(Orga_Model_Member::QUERY_REF);
        $members = Orga_Model_Member::loadList($this->request);

        $idCell = $this->getParam('idCell');
        if (!empty($idCell)) {
            $cell = Orga_Model_Cell::load($idCell);
            foreach ($cell->getMembers() as $cellMember) {
                $cellMember->getAxis()->getRef();
                if ($cellMember->getAxis()->isBroaderThan($axis)) {
                    $members = array_intersect($members, $cellMember->getChildrenForAxis($axis));
                }
            }
        }

        foreach ($members as $member) {
            $data = array();
            /** @var $member Orga_Model_Member */
            $data['index'] = $member->getId();
            $data['label'] = $this->cellText($member->getLabel());
            $data['ref'] = $this->cellText($member->getRef());
            $parentMembers = $member->getDirectParents();
            foreach ($axis->getDirectBroaders() as $broaderAxis) {
                $cellAxis = $this->cellList(null, '');
                foreach ($parentMembers as $parentMember) {
                    if (in_array($parentMember, $broaderAxis->getMembers())) {
                        $cellAxis = $this->cellList($parentMember->getRef(), $parentMember->getLabel());
                    }
                }
                $data['broader'.$broaderAxis->getRef()] = $cellAxis;
            }
            $this->addLine($data);
        }
        if (empty($idCell)) {
            $this->totalElements = Orga_Model_Member::countTotal($this->request);
        }

        $this->send();
    }


    /**
     * Ajoute un nouvel element.
     * @Secure("editMembers")
     */
    public function addelementAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));
        $axis = Orga_Model_Axis::loadByRefAndOrganization($this->getParam('refAxis'), $organization);

        $label = $this->getAddElementValue('label');
        $ref = $this->getAddElementValue('ref');

        try {
            Core_Tools::checkRef($ref);
        } catch (Core_Exception_User $e) {
            $this->setAddElementErrorMessage('ref', $e->getMessage());
        }

        $broaderMembers = array();
        $contextualizingMembers = array();
        foreach ($axis->getDirectBroaders() as $directBroaderAxis) {
            $formFieldRef = 'broader'.$directBroaderAxis->getRef();
            $refBroaderMember = $this->getAddElementValue($formFieldRef);
            if (empty($refBroaderMember)) {
                continue;
            } else {
                try {
                    $broaderMember = Orga_Model_Member::loadByCompleteRefAndAxis($refBroaderMember, $directBroaderAxis);
                    $broaderMembers[] = $broaderMember;
                } catch (Core_Exception_NotFound $e) {
                    $this->setAddElementErrorMessage($formFieldRef, __('UI', 'exception', 'unknownError'));
                    continue;
                }
                if ($broaderMember->getAxis()->isContextualizing()) {
                    $contextualizingMembers[] = $broaderMember;
                }
                $contextualizingMembers = array_merge(
                    $contextualizingMembers,
                    $broaderMember->getContextualizingParents()
                );
            }
        }

        if (empty($this->_addErrorMessages)) {
            try {
                Orga_Model_Member::loadByCompleteRefAndAxis(
                    $ref . '#' . Orga_Model_Member::buildParentMembersHashKey($contextualizingMembers),
                    $axis
                );
                $this->setAddElementErrorMessage('ref', __('UI', 'formValidation', 'alreadyUsedIdentifier'));
            } catch (Core_Exception_NotFound $e) {
                $this->workDispatcher->runBackground(
                    new Orga_Work_Task_AddMember(
                        $axis,
                        $ref,
                        $label,
                        $broaderMembers,
                        __('Orga', 'backgroundTasks', 'addMember', ['MEMBER' => $label, 'AXIS' => $axis->getLabel()])
                    )
                );
                $this->message = __('UI', 'message', 'addedLater');
            }
        }

        $this->send();
    }

    /**
     * Supprime un element.
     * @Secure("editMembers")
     */
    public function deleteelementAction()
    {
        $member = Orga_Model_Member::load($this->delete);

        if ($member->hasDirectChildren()) {
            throw new Core_Exception_User('Orga', 'member', 'memberHasChild');
        }

        try {
            $this->entityManager->beginTransaction();

            $member->delete();

            $this->entityManager->flush();
            $this->entityManager->commit();

            $this->message = __('UI', 'message', 'deleted', array('LABEL' => $member->getLabel()));
        } catch (ErrorException $e) {
            $this->entityManager->rollback();

            throw new Core_Exception_User('Orga', 'member', 'deleteMemberWithUsersToCells');
        }

        $this->send();
    }

    /**
     * Modifie les valeurs d'un element.
     * @Secure("editMembers")
     */
    public function updateelementAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));
        $axis = Orga_Model_Axis::loadByRefAndOrganization($this->getParam('refAxis'), $organization);
        $member = Orga_Model_Member::load($this->update['index']);

        switch ($this->update['column']) {
            case 'label':
                $member->setLabel($this->update['value']);
                $this->message = __('UI', 'message', 'updated', array('LABEL' => $member->getLabel()));
                break;
            case 'ref':
                Core_Tools::checkRef($this->update['value']);
                try {
                    $completeRef = Orga_Model_Member::buildParentMembersHashKey($member->getContextualizingParents());
                    $completeRef = $this->update['value'] . '#' . $completeRef;
                    if (Orga_Model_Member::loadByCompleteRefAndAxis($completeRef, $axis) !== $member) {
                        throw new Core_Exception_User('UI', 'formValidation', 'alreadyUsedIdentifier');
                    }
                } catch (Core_Exception_NotFound $e) {
                    $member->setRef($this->update['value']);
                    $this->message = __('UI', 'message', 'updated', array('LABEL' => $member->getLabel()));
                }
                break;
            default:
                $refBroaderAxis = substr($this->update['column'], 7);
                try {
                    $broaderAxis = Orga_Model_Axis::loadByRefAndOrganization($refBroaderAxis, $organization);
                } catch (Core_Exception_NotFound $e) {
                    parent::updateelementAction();
                }
                foreach ($member->getDirectParents() as $parentMember) {
                    if (($parentMember->getAxis()->getRef() === $refBroaderAxis)
                        && ($parentMember->getRef() === $this->update['value'])) {
                        break 2;
                    } else if ($parentMember->getAxis()->getRef() === $refBroaderAxis) {
                        $member->removeDirectParent($parentMember);
                    }
                }
                if (!empty($this->update['value'])) {
                    $parentMember = Orga_Model_Member::loadByCompleteRefAndAxis($this->update['value'], $broaderAxis);
                    $member->addDirectParent($parentMember);
                }
                $this->message = __('UI', 'message', 'updated', array('LABEL' => $member->getLabel()));
                break;
        }
        $this->data = $this->update['value'];

        $this->send();
    }

    /**
     * Renvoie la liste des parents éligibles pour un membre.
     * @Secure("viewMembers")
     */
    public function getparentsAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));
        $broaderAxis = Orga_Model_Axis::loadByRefAndOrganization($this->getParam('refParentAxis'), $organization);

        $members = $broaderAxis->getMembers();
        $idCell = $this->getParam('idCell');
        if (!empty($idCell)) {
            $cell = Orga_Model_Cell::load($idCell);
            foreach ($cell->getMembers() as $cellMember) {
                $cellMember->getAxis()->getRef();
                if ($cellMember->getAxis()->isBroaderThan($broaderAxis)) {
                    $members = array_intersect($members, $cellMember->getChildrenForAxis($broaderAxis));
                } else if ($cellMember->getAxis() === $broaderAxis) {
                    $members = array($cellMember);
                    break;
                }
            }
        }

        $query = $this->getParam('q');
        if (!empty($query)) {
            foreach ($members as $indexMember => $member) {
                if (strpos($member->getLabel(), $query) === false) {
                    unset($members[$indexMember]);
                }
            }
        }

        $this->addElementAutocompleteList('', '');
        foreach ($members as $eligibleParentMember) {
            $this->addElementAutocompleteList($eligibleParentMember->getCompleteRef(), $eligibleParentMember->getLabel());
        }

        $this->send();
    }

}