<?php
/**
 * Classe Classification_Datagrid_MemberController
 * @author valentin.claras
 * @author cyril.perraud
 * @package Classification
 * @subpackage Controller
 */

use Classification\Domain\Member;
use Classification\Domain\Axis;
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
     * @Secure("viewClassificationLibrary")
     */
    public function getelementsAction()
    {
        $axis = Axis::load($this->getParam('axis'));

        $this->request->filter->addCondition(Member::QUERY_AXIS, $axis);
        foreach (Member::loadList($this->request) as $member) {
            $data = array();
            $data['index'] = $member->getId();
            $data['label'] = $this->cellTranslatedText($member->getLabel());
            $data['ref'] = $this->cellText($member->getRef());
            $canUp = !($member->getPosition() === 1);
            $canDown = !($member->getPosition() === $member->getLastEligiblePosition());
            $data['position'] = $this->cellPosition($member->getPosition(), $canUp, $canDown);
            $parentMembers = $member->getDirectParents();
            foreach ($axis->getDirectBroaders() as $broaderAxis) {
                $cellAxis = $this->cellList(null, '');
                foreach ($parentMembers as $parentMember) {
                    if (in_array($parentMember, $broaderAxis->getMembers())) {
                        $cellAxis = $this->cellList($parentMember->getId(), $parentMember->getLabel());
                    }
                }
                $data['broader'.$broaderAxis->getId()] = $cellAxis;
            }
            $this->addLine($data);
        }
        $this->totalElements = Member::countTotal($this->request);

        $this->send();
    }


    /**
     * Ajoute un nouvel element.
     *
     * @Secure("editClassificationLibrary")
     */
    public function addelementAction()
    {
        $axis = Axis::load($this->getParam('axis'));
        $label = $this->getAddElementValue('label');
        $ref = $this->getAddElementValue('ref');
        try {
            Core_Tools::checkRef($ref);
        } catch (Core_Exception_User $e) {
            $this->setAddElementErrorMessage('ref', $e->getMessage());
        }

        $broaderMembers = array();
        foreach ($axis->getDirectBroaders() as $directBroader) {
            $formFieldRef = 'broader'.$directBroader->getId();
            $idBroaderMember = $this->getAddElementValue($formFieldRef);
            if (empty($idBroaderMember)) {
                $this->setAddElementErrorMessage($formFieldRef, __('UI', 'formValidation', 'emptyRequiredField'));
            } else {
                try {
                    $broaderMembers[] = Member::load($idBroaderMember);
                } catch (Core_Exception_NotFound $e) {
                    $this->setAddElementErrorMessage($formFieldRef, __('Core', 'exception', 'applicationError'));
                }
            }
        }

        try {
            $axis->getMemberByRef($ref);
            $this->setAddElementErrorMessage('ref', __('UI', 'formValidation', 'alreadyUsedIdentifier'));
        } catch (Core_Exception_NotFound $e) {
            if (empty($this->_addErrorMessages)) {
                $member = new Member();
                $member->setRef($ref);
                $this->translationHelper->set($member->getLabel(), $label);
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
     * @Secure("editClassificationLibrary")
     */
    public function deleteelementAction()
    {
        $member = Member::load($this->delete);
        $member->delete();
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

    /**
     * Modifie les valeurs d'un element.
     *
     * @Secure("editClassificationLibrary")
     */
    public function updateelementAction()
    {
        $axis = Axis::load($this->getParam('axis'));
        $member = Member::load($this->update['index']);

        switch ($this->update['column']) {
            case 'label':
                $this->translationHelper->set($member->getLabel(), $this->update['value']);
                $this->message = __('UI', 'message', 'updated');
                break;
            case 'ref':
                Core_Tools::checkRef($this->update['value']);
                try {
                    if ($axis->getMemberByRef($this->update['value']) !== $member) {
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
                    $broaderAxisId = substr($this->update['column'], 7);
                    foreach ($member->getDirectParents() as $parentMember) {
                        if (($parentMember->getAxis()->getId() === $broaderAxisId)
                                && ($parentMember->getId() === $this->update['value'])) {
                            break 2;
                        } else if ($parentMember->getAxis()->getId() === $broaderAxisId) {
                            $member->removeDirectParent($parentMember);
                        }
                    }
                    $parentMember = Member::load($this->update['value']);
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
     * @Secure("editClassificationLibrary")
     */
    public function getparentsAction()
    {
        $broaderAxis = Axis::load($this->getParam('parentAxis'));

        if (($this->hasParam('source')) && ($this->getParam('source') === 'add')) {
            $this->addElementList(0, '');
        }
        foreach ($broaderAxis->getMembers() as $eligibleParentMember) {
            $this->addElementList(
                $eligibleParentMember->getId(),
                $this->translationHelper->toString($eligibleParentMember->getLabel())
            );
        }

        $this->send();
    }

}
