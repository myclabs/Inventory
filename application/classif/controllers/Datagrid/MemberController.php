<?php
/**
 * Classe Classif_Datagrid_MemberController
 * @author valentin.claras
 * @author cyril.perraud
 * @package Classif
 */

use Core\Annotation\Secure;

/**
 * Enter description here ...
 * @package Classif
 */
class Classif_Datagrid_MemberController extends UI_Controller_Datagrid
{
    /**
     * Methode appelee pour remplir le tableau.
     *
     * @Secure("viewClassif")
     */
    public function getelementsAction()
    {
        $axis = Classif_Model_Axis::loadByRef($this->_getParam('refAxis'));

        $this->request->filter->addCondition(Classif_Model_Member::QUERY_AXIS, $axis);
        foreach (Classif_Model_Member::loadList($this->request) as $member) {
            $data = array();
            $data['index'] = $member->getRef();
            $data['label'] = $this->cellText($member->getLabel());
            $data['ref'] = $this->cellText($member->getRef());
            $canUp = !($member->getPosition() === 1);
            $canDown = !($member->getPosition() === $member->getLastEligiblePosition());
            $data['position'] = $this->cellPosition($member->getPosition(), $canUp, $canDown);
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
        $this->totalElements = Classif_Model_Member::countTotal($this->request);

        $this->send();
    }


    /**
     * Ajoute un nouvel element.
     *
     * @Secure("editClassif")
     */
    public function addelementAction()
    {
        $axis = Classif_Model_Axis::loadByRef($this->_getParam('refAxis'));
        $label = $this->getAddElementValue('label');
        $ref = $this->getAddElementValue('ref');
        try {
            Core_Tools::checkRef($ref);
        } catch (Core_Exception_User $e) {
            $this->setAddElementErrorMessage('ref', $e->getMessage());
        }

        $broaderMembers = array();
        foreach ($axis->getDirectBroaders() as $directBroader) {
            $formFieldRef = 'broader'.$directBroader->getRef();
            $refBroaderMember = $this->getAddElementValue($formFieldRef);
            if (empty($refBroaderMember)) {
                $this->setAddElementErrorMessage($formFieldRef, __('UI', 'formValidation', 'emptyRequiredField'));
            } else {
                try {
                    $broaderMembers[] = Classif_Model_Member::loadByRefAndAxis($refBroaderMember, $directBroader);
                } catch (Core_Exception_NotFound $e) {
                    $this->setAddElementErrorMessage($formFieldRef, __('UI', 'exception', 'unknownError'));
                }
            }
        }

        try {
            Classif_Model_Member::loadByRefAndAxis($ref, $axis);
            $this->setAddElementErrorMessage('ref', __('UI', 'formValidation', 'alreadyUsedIdentifier'));
        } catch (Core_Exception_NotFound $e) {
            if (empty($this->_addErrorMessages)) {
                $member = new Classif_Model_Member();
                $member->setRef($ref);
                $member->setLabel($label);
                $member->setAxis($axis);
                foreach ($broaderMembers as $broaderMember) {
                    $member->addDirectParent($broaderMember);
                }
                $member->save();
                $this->message = __('UI', 'message', 'added');
            }
        }

        $this->send();
    }

    /**
     * Supprime un element.
     *
     * @Secure("editClassif")
     */
    public function deleteelementAction()
    {
        $axis = Classif_Model_Axis::loadByRef($this->_getParam('refAxis'));
        $member = Classif_Model_Member::loadByRefAndAxis($this->delete, $axis);
        $member->delete();
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

    /**
     * Modifie les valeurs d'un element.
     *
     * @Secure("editClassif")
     */
    public function updateelementAction()
    {
        $axis = Classif_Model_Axis::loadByRef($this->_getParam('refAxis'));
        $member = Classif_Model_Member::loadByRefAndAxis($this->update['index'], $axis);

        switch ($this->update['column']) {
            case 'label':
                $member->setLabel($this->update['value']);
                $this->message = __('UI', 'message', 'updated');
                break;
            case 'ref':
                Core_Tools::checkRef($this->update['value']);
                try {
                    if (Classif_Model_Member::loadByRefAndAxis($this->update['value'], $axis) !== $member) {
                        throw new Core_Exception_User('UI', 'formValidation', 'alreadyUsedIdentifier');
                    }
                } catch (Core_Exception_NotFound $e) {
                    $member->setRef($this->update['value']);
                    $this->message = __('UI', 'message', 'updated');
                }
                break;
            case 'position':
                switch ($this->update['value']) {
                    case 'goFirst':
                        $member->setPosition(1);
                        break;
                    case 'goUp':
                        $member->goUp();
                        break;
                    case 'goDown':
                        $member->goDown();
                        break;
                    case 'goLast':
                        $member->setPosition($member->getLastEligiblePosition());
                        break;
                    default:
                        if ($this->update['value'] > $member->getLastEligiblePosition()) {
                            $this->update['value'] = $member->getLastEligiblePosition();
                        }
                        $member->setPosition((int) $this->update['value']);
                        break;
                }
                $this->update['value'] = $member->getPosition();
                $this->message = __('UI', 'message', 'updated');
                break;
            default:
                try {
                    $refBroaderAxis = substr($this->update['column'], 7);
                    $broaderAxis = Classif_Model_Axis::loadByRef($refBroaderAxis);
                    foreach ($member->getDirectParents() as $parentMember) {
                        if (($parentMember->getAxis()->getRef() === $refBroaderAxis)
                                && ($parentMember->getRef() === $this->update['value'])) {
                            break 2;
                        } else if ($parentMember->getAxis()->getRef() === $refBroaderAxis) {
                            $member->removeDirectParent($parentMember);
                        }
                    }
                    $parentMember = Classif_Model_Member::loadByRefAndAxis($this->update['value'], $broaderAxis);
                    $member->addDirectParent($parentMember);
                    $this->message = __('UI', 'message', 'updated');
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
     *
     * @Secure("editClassif")
     */
    public function getparentsAction()
    {
        $broaderAxis = Classif_Model_Axis::loadByRef($this->_getParam('refParentAxis'));

        if (($this->_hasParam('source')) && ($this->_getParam('source') === 'add')) {
            $this->addElementList('', '');
        }
        foreach ($broaderAxis->getMembers() as $eligibleParentMember) {
            $this->addElementList($eligibleParentMember->getRef(), $eligibleParentMember->getLabel());
        }

        $this->send();
    }

}