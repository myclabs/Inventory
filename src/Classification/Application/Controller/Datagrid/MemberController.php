<?php
/**
 * Classe Classification_Datagrid_MemberController
 * @author valentin.claras
 * @author cyril.perraud
 * @package Classification
 * @subpackage Controller
 */

use Classification\Domain\AxisMember;
use Classification\Domain\IndicatorAxis;
use Core\Annotation\Secure;

/**
 * Classe du controller du datagrid des indicateurs
 * @package Classification
 * @subpackage Controller
 */
class Classification_Datagrid_MemberController extends UI_Controller_Datagrid
{
    /**
     * Methode appelee pour remplir le tableau.
     *
     * @Secure("viewClassification")
     */
    public function getelementsAction()
    {
        $axis = IndicatorAxis::loadByRef($this->getParam('refAxis'));

        $this->request->filter->addCondition(AxisMember::QUERY_AXIS, $axis);
        foreach (AxisMember::loadList($this->request) as $member) {
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
        $this->totalElements = AxisMember::countTotal($this->request);

        $this->send();
    }


    /**
     * Ajoute un nouvel element.
     *
     * @Secure("editClassification")
     */
    public function addelementAction()
    {
        $axis = IndicatorAxis::loadByRef($this->getParam('refAxis'));
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
                    $broaderMembers[] = AxisMember::loadByRefAndAxis($refBroaderMember, $directBroader);
                } catch (Core_Exception_NotFound $e) {
                    $this->setAddElementErrorMessage($formFieldRef, __('Core', 'exception', 'applicationError'));
                }
            }
        }

        try {
            AxisMember::loadByRefAndAxis($ref, $axis);
            $this->setAddElementErrorMessage('ref', __('UI', 'formValidation', 'alreadyUsedIdentifier'));
        } catch (Core_Exception_NotFound $e) {
            if (empty($this->_addErrorMessages)) {
                $member = new AxisMember();
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
     * @Secure("editClassification")
     */
    public function deleteelementAction()
    {
        $axis = IndicatorAxis::loadByRef($this->getParam('refAxis'));
        $member = AxisMember::loadByRefAndAxis($this->delete, $axis);
        $member->delete();
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

    /**
     * Modifie les valeurs d'un element.
     *
     * @Secure("editClassification")
     */
    public function updateelementAction()
    {
        $axis = IndicatorAxis::loadByRef($this->getParam('refAxis'));
        $member = AxisMember::loadByRefAndAxis($this->update['index'], $axis);

        switch ($this->update['column']) {
            case 'label':
                $member->setLabel($this->update['value']);
                $this->message = __('UI', 'message', 'updated');
                break;
            case 'ref':
                Core_Tools::checkRef($this->update['value']);
                try {
                    if (AxisMember::loadByRefAndAxis($this->update['value'], $axis) !== $member) {
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
                    $broaderAxis = IndicatorAxis::loadByRef($refBroaderAxis);
                    foreach ($member->getDirectParents() as $parentMember) {
                        if (($parentMember->getAxis()->getRef() === $refBroaderAxis)
                                && ($parentMember->getRef() === $this->update['value'])) {
                            break 2;
                        } else if ($parentMember->getAxis()->getRef() === $refBroaderAxis) {
                            $member->removeDirectParent($parentMember);
                        }
                    }
                    $parentMember = AxisMember::loadByRefAndAxis($this->update['value'], $broaderAxis);
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
     * @Secure("editClassification")
     */
    public function getparentsAction()
    {
        $broaderAxis = IndicatorAxis::loadByRef($this->getParam('refParentAxis'));

        if (($this->hasParam('source')) && ($this->getParam('source') === 'add')) {
            $this->addElementList('', '');
        }
        foreach ($broaderAxis->getMembers() as $eligibleParentMember) {
            $this->addElementList($eligibleParentMember->getRef(), $eligibleParentMember->getLabel());
        }

        $this->send();
    }

}
