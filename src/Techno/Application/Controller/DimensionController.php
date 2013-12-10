<?php

use Core\Annotation\Secure;
use Techno\Domain\Family\Family;
use Techno\Domain\Family\Member;

/**
 * @author matthieu.napoli
 */
class Techno_DimensionController extends Core_Controller
{
    /**
     * Ajout de membres
     * @Secure("editTechno")
     */
    public function addMembersAction()
    {
        $family = Family::load($this->getParam('idFamily'));
        $dimensionRef = $this->getParam('dimension');
        $dimension = $family->getDimension($dimensionRef);

        $memberList = trim($this->getParam('inputMemberList'));

        $lines = preg_split('/$\R?^/m', $memberList);

        $number = 0;

        foreach ($lines as $line) {
            $array = explode(',', $line);

            if (count($array) !== 2) {
                UI_Message::addMessageStatic(__('Techno', 'import', 'invalidMembersInput'));
                $this->redirect('techno/family/edit/id/' . $family->getId());
                return;
            }

            list($ref, $label) = $array;
            $ref = trim($ref);
            $label = trim($label);

            try {
                $member = new Member($dimension, $ref, $label);
            } catch (Core_Exception_User $e) {
                UI_Message::addMessageStatic($e->getMessage());
                $this->redirect('techno/family/edit/id/' . $family->getId());
                return;
            }
            $dimension->addMember($member);
            $member->save();

            $number++;
        }

        try {
            $this->entityManager->flush();
        } catch (Core_ORM_DuplicateEntryException $e) {
            UI_Message::addMessageStatic(__('Techno', 'import', 'dimensionHasMember', ['REF' => $e->getEntry()]));
            $this->redirect('techno/family/edit/id/' . $family->getId());
            return;
        }

        UI_Message::addMessageStatic(
            __('Techno', 'import', 'importSuccessful', ['NUMBER' => $number]),
            UI_Message::TYPE_SUCCESS
        );
        $this->redirect('techno/family/edit/id/' . $family->getId());
    }
}
