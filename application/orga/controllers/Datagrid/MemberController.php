<?php
/**
 * Classe Orga_Datagrid_MemberController
 * @author valentin.claras
 * @author cyril.perraud
 * @package Orga
 */

use Core\Annotation\Secure;

/**
 * Enter description here ...
 * @package Orga
 */
class Orga_Datagrid_MemberController extends UI_Controller_Datagrid
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
     * @Secure("viewProject")
     */
    public function getelementsAction()
    {
        $project = Orga_Model_Project::load($this->getParam('idProject'));
        $axis = Orga_Model_Axis::loadByRefAndProject($this->getParam('refAxis'), $project);

        $this->request->filter->addCondition(Orga_Model_Member::QUERY_AXIS, $axis);
        $this->request->order->addOrder(Orga_Model_Member::QUERY_REF);
        $members = Orga_Model_Member::loadList($this->request);

        $idFilterCell = $this->getParam('idFilterCell');
        if (!empty($idFilterCell)) {
            $filterCell = Orga_Model_Cell::load(array('id' => $idFilterCell));
            foreach ($filterCell->getMembers() as $cellMember) {
                $cellMember->getAxis()->getRef();
                if ($cellMember->getAxis()->isBroaderThan($axis)) {
                    $members = array_intersect($members, $cellMember->getChildrenForAxis($axis));
                }
            }
        }

        foreach ($members as $member) {
            $data = array();
            /** @var $member Orga_Model_Member */
            $data['index'] = $member->getCompleteRef();
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
        if (empty($idFilterCell)) {
            $this->totalElements = Orga_Model_Member::countTotal($this->request);
        }

        $this->send();
    }


    /**
     * Ajoute un nouvel element.
     * @Secure("editProject")
     */
    public function addelementAction()
    {
        $project = Orga_Model_Project::load($this->getParam('idProject'));
        $axis = Orga_Model_Axis::loadByRefAndProject($this->getParam('refAxis'), $project);

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
                $this->setAddElementErrorMessage($formFieldRef, __('UI', 'formValidation', 'emptyRequiredField'));
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
                /**@var Core_Work_Dispatcher $dispatcher */
                $dispatcher = Zend_Registry::get('workDispatcher');
                $dispatcher->runBackground(
                    new Orga_Work_Task_AddMember(
                        $axis,
                        $ref,
                        $label,
                        $broaderMembers
                    )
                );
                $this->message = __('UI', 'message', 'addedLater');
            }
        }

        $this->send();
    }

    /**
     * Supprime un element.
     * @Secure("editProject")
     */
    public function deleteelementAction()
    {
        $project = Orga_Model_Project::load($this->getParam('idProject'));
        $axis = Orga_Model_Axis::loadByRefAndProject($this->getParam('refAxis'), $project);
        $member = Orga_Model_Member::loadByCompleteRefAndAxis($this->delete, $axis);

        if ($member->hasDirectChildren()) {
            throw new Core_Exception_User('Orga', 'member', 'memberHasChild');
        }

        $member->delete();
        $this->message = __('UI', 'message', 'deleted', array('LABEL' => $member->getLabel()));
        $this->send();
    }

    /**
     * Modifie les valeurs d'un element.
     * @Secure("editProject")
     */
    public function updateelementAction()
    {
        $project = Orga_Model_Project::load($this->getParam('idProject'));
        $axis = Orga_Model_Axis::loadByRefAndProject($this->getParam('refAxis'), $project);
        $member = Orga_Model_Member::loadByCompleteRefAndAxis($this->update['index'], $axis);

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
                try {
                    $refBroaderAxis = substr($this->update['column'], 7);
                    $broaderAxis = Orga_Model_Axis::loadByRefAndProject($refBroaderAxis, $project);
                    foreach ($member->getDirectParents() as $parentMember) {
                        if (($parentMember->getAxis()->getRef() === $refBroaderAxis)
                                && ($parentMember->getRef() === $this->update['value'])) {
                            break 2;
                        } else if ($parentMember->getAxis()->getRef() === $refBroaderAxis) {
                            $member->removeDirectParent($parentMember);
                        }
                    }
                    $parentMember = Orga_Model_Member::loadByCompleteRefAndAxis($this->update['value'], $broaderAxis);
                    $member->addDirectParent($parentMember);
                    $this->message = __('UI', 'message', 'updated', array('LABEL' => $member->getLabel()));
                } catch (Core_Exception_NotFound $e) {
                   parent::updateelementAction();
                }
                break;
        }
        $this->data = $this->update['value'];

        $this->send();
    }

    /**
     * Renvoie la liste des parents éligibles pour un membre.
     * @Secure("viewProject")
     */
    public function getparentsAction()
    {
        $project = Orga_Model_Project::load($this->getParam('idProject'));
        $broaderAxis = Orga_Model_Axis::loadByRefAndProject($this->getParam('refParentAxis'), $project);

        $members = $broaderAxis->getMembers();
        $idFilterCell = $this->getParam('idFilterCell');
        if (!empty($idFilterCell)) {
            $filterCell = Orga_Model_Cell::load(array('id' => $idFilterCell));
            foreach ($filterCell->getMembers() as $cellMember) {
                $cellMember->getAxis()->getRef();
                if ($cellMember->getAxis()->isBroaderThan($broaderAxis)) {
                    $members = array_intersect($members, $cellMember->getChildrenForAxis($broaderAxis));
                } else if ($cellMember->getAxis() === $broaderAxis) {
                    $members = array($cellMember);
                    break;
                }
            }
        }

        if (($this->hasParam('source')) && ($this->getParam('source') === 'add') && (count($members) > 1)) {
            $this->addElementList('', '');
        }
        foreach ($members as $eligibleParentMember) {
            $this->addElementList($eligibleParentMember->getCompleteRef(), $eligibleParentMember->getLabel());
        }

        $this->send();
    }

}