<?php
/**
 * @author matthieu.napoli
 * @package Techno
 */

use Core\Annotation\Secure;

/**
 * @package Techno
 */
class Techno_Datagrid_Dimension_MembersDatagridController extends UI_Controller_Datagrid
{

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::getelementsAction()
     * @Secure("editTechno")
     */
    public function getelementsAction()
    {
        /** @var $dimension Techno_Model_Family_Dimension */
        $dimension = Techno_Model_Family_Dimension::load($this->getParam('id'));
        $members = $dimension->getMembers();

        foreach ($members as $member) {
            $data = [];
            $data['index'] = $member->getId();
            $data['label'] = $member->getLabel();
            $data['refKeyword'] = $this->cellList($member->getRef());
            // Seule les valeurs en erreur sont éditables
            $this->editableCell($data['refKeyword'], false);
            try {
                $member->getKeyword();
            } catch (Core_Exception_NotFound $e) {
                $this->editableCell($data['refKeyword'], true);
            }
            // Position
            $canMoveUp = ($member->getPosition() > 1);
            $canMoveDown = ($member->getPosition() < $member->getLastEligiblePosition());
            $data['position'] = $this->cellPosition($member->getPosition(), $canMoveUp, $canMoveDown);
            $this->addLine($data);
        }

        $this->totalElements = count($members);
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::addelementAction()
     * @Secure("editTechno")
     */
    public function addelementAction()
    {
        /** @var $dimension Techno_Model_Family_Dimension */
        $dimension = Techno_Model_Family_Dimension::load($this->getParam('id'));
        // Validation du formulaire
        $refKeyword = $this->getAddElementValue('refKeyword');
        if (empty($refKeyword)) {
            $this->setAddElementErrorMessage('refKeyword', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        try {
            $keyword = Keyword_Model_Keyword::loadByRef($refKeyword);
        } catch(Core_Exception_NotFound $e) {
            $this->setAddElementErrorMessage('refKeyword', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        // Pas d'erreurs
        if (empty($this->_addErrorMessages)) {
            try {
                /** @noinspection PhpUndefinedVariableInspection */
                $member = new Techno_Model_Family_Member($dimension, $keyword);
                $member->save();
                $dimension->addMember($member);
                $dimension->save();
                $this->entityManager->flush();
            } catch (Core_ORM_DuplicateEntryException $e) {
                $this->setAddElementErrorMessage('refKeyword', __('Techno', 'dimension', 'dimensionHasKeyword'));
                $this->send();
                return;
            }
            $this->message = __('UI', 'message', 'added');
        }
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::updateelementAction()
     * @Secure("editTechno")
     */
    public function updateelementAction()
    {
        /** @var $member Techno_Model_Family_Member */
        $member = Techno_Model_Family_Member::load($this->update['index']);
        $newValue = $this->update['value'];
        switch($this->update['column']) {
            case 'position':
                $oldPosition = $member->getPosition();
                switch ($newValue) {
                    case 'goFirst':
                        $newPosition = 1;
                        break;
                    case 'goUp':
                        $newPosition = $oldPosition - 1;
                        break;
                    case 'goDown':
                        $newPosition = $oldPosition + 1;
                        break;
                    case 'goLast':
                        $newPosition = $member->getLastEligiblePosition();
                        break;
                    default:
                        $newPosition = $newValue;
                        break;
                }
                $member->setPosition($newPosition);
                break;
            case 'refKeyword':
                try {
                    $keyword = Keyword_Model_Keyword::loadByRef($newValue);
                    $member->setKeyword($keyword);
                    $this->data = $keyword->getRef();
                } catch (Core_Exception_NotFound $e) {
                    throw new Core_Exception_User('Techno', 'formValidation', 'unknownKeywordRef');
                }
                break;
        }
        $member->save();
        $this->entityManager->flush();
        $this->message = __('UI', 'message', 'updated');
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::deleteelementAction()
     * @Secure("editTechno")
     */
    public function deleteelementAction()
    {
        /** @var $member Techno_Model_Family_Member */
        $member = Techno_Model_Family_Member::load($this->getParam('index'));
        $dimension = $member->getDimension();
        $dimension->removeMember($member);
        $dimension->save();
        $this->entityManager->flush();
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

}
