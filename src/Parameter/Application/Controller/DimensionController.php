<?php

use Core\Annotation\Secure;
use Core\Translation\TranslatedString;
use Parameter\Domain\Family\Dimension;
use Parameter\Domain\Family\Family;
use Parameter\Domain\Family\Member;

/**
 * @author matthieu.napoli
 */
class Parameter_DimensionController extends Core_Controller
{
    /**
     * Ajout d'une dimension
     * @Secure("editParameterFamily")
     */
    public function addAction()
    {
        $family = Family::load($this->getParam('idFamily'));

        $ref = trim($this->getParam('ref'));
        $label = trim($this->getParam('label'));
        $orientation = $this->getParam('orientation');

        try {
            Core_Tools::checkRef($ref);
        } catch (Core_Exception_User $e) {
            UI_Message::addMessageStatic($e->getMessage());
            $this->redirect('parameter/family/edit/id/' . $family->getId());
            return;
        }

        $label = $this->translator->set(new TranslatedString(), $label);
        $dimension = new Dimension($family, $ref, $label, $orientation);
        $dimension->save();
        $family->addDimension($dimension);
        $this->entityManager->flush();

        UI_Message::addMessageStatic(__('UI', 'message', 'added'), UI_Message::TYPE_SUCCESS);
        $this->redirect('parameter/family/edit/id/' . $family->getId());
    }

    /**
     * Édition d'une dimension
     * @Secure("editParameterFamily")
     */
    public function editAction()
    {
        $family = Family::load($this->getParam('idFamily'));

        $ref = trim($this->getParam('ref'));
        $label = trim($this->getParam('label'));

        try {
            Core_Tools::checkRef($ref);
        } catch (Core_Exception_User $e) {
            UI_Message::addMessageStatic($e->getMessage());
            $this->redirect('parameter/family/edit/id/' . $family->getId());
            return;
        }

        $dimension = $family->getDimension($this->getParam('refDimension'));
        $dimension->setRef($ref);
        $this->translator->set($dimension->getLabel(), $label);
        $this->entityManager->flush();

        UI_Message::addMessageStatic(__('UI', 'message', 'updated'), UI_Message::TYPE_SUCCESS);
        $this->redirect('parameter/family/edit/id/' . $family->getId());
    }

    /**
     * Suppression d'une dimension
     * @Secure("editParameterFamily")
     */
    public function deleteAction()
    {
        $dimension = Dimension::load($this->getParam('idFamily'));
        $dimension->getFamily()->removeDimension($dimension);
        $dimension->delete();
        $this->entityManager->flush();

        UI_Message::addMessageStatic(__('UI', 'message', 'deleted'), UI_Message::TYPE_SUCCESS);
        $this->redirect('parameter/family/edit/id/' . $dimension->getFamily()->getId());
    }

    /**
     * Ajout de membres
     * @Secure("editParameterFamily")
     */
    public function addMembersAction()
    {
        $family = Family::load($this->getParam('idFamily'));
        $dimensionRef = $this->getParam('dimension');
        $dimension = $family->getDimension($dimensionRef);

        $memberList = trim($this->getParam('members'));

        $lines = preg_split('/$\R?^/m', $memberList);

        $number = 0;

        foreach ($lines as $line) {
            $array = preg_split('/[\t;]/', $line);

            if (count($array) > 2) {
                UI_Message::addMessageStatic(__('Parameter', 'import', 'invalidMembersInput'));
                $this->redirect('parameter/family/edit/id/' . $family->getId());
                return;
            }

            if (count($array) === 2) {
                list($label, $ref) = $array;
                $label = trim($label);
                $ref = trim($ref);
            } else {
                $label = trim(reset($array));
                $ref = '';
            }
            if (empty($ref)) {
                $ref = Core_Tools::refactor($label);
            }

            try {
                $label = $this->translator->set(new TranslatedString(), $label);
                $member = new Member($dimension, $ref, $label);
            } catch (Core_Exception_User $e) {
                UI_Message::addMessageStatic($e->getMessage());
                $this->redirect('parameter/family/edit/id/' . $family->getId());
                return;
            }
            $dimension->addMember($member);
            $member->save();

            $number++;
        }

        try {
            $this->entityManager->flush();
        } catch (Core_ORM_DuplicateEntryException $e) {
            UI_Message::addMessageStatic(__('Parameter', 'import', 'dimensionHasMember', ['REF' => $e->getEntry()]));
            $this->redirect('parameter/family/edit/id/' . $family->getId());
            return;
        }

        UI_Message::addMessageStatic(
            __('Parameter', 'import', 'importSuccessful', ['NUMBER' => $number]),
            UI_Message::TYPE_SUCCESS
        );
        $this->redirect('parameter/family/edit/id/' . $family->getId());
    }
}
