<?php

use Core\Annotation\Secure;
use Parameter\Domain\Family\Family;

/**
 * @author matthieu.napoli
 */
class Parameter_EditElementsController extends Core_Controller
{
    /**
     * Edition
     * @Secure("editParameterFamily")
     */
    public function editElementsAction()
    {
        $locale = Core_Locale::loadDefault();

        $family = Family::load($this->getParam('idFamily'));
        $dimensions = $family->getDimensions();

        $elements = trim($this->getParam('elements'));

        $lines = preg_split('/$\R?^/m', $elements);

        $number = 0;

        foreach ($lines as $line) {
            $array = preg_split('/[\t;]/', $line);

            if (count($array) !== (count($dimensions) + 2)) {
                UI_Message::addMessageStatic(__('Parameter', 'import', 'invalidElementsInput'));
                $this->redirect('parameter/family/edit/id/' . $family->getId());
                return;
            }

            $membersRef = array_slice($array, 0, count($array) - 2);
            $digitalValue = $locale->readNumber(trim($array[count($array) - 2]));
            $uncertainty = $locale->readInteger(trim($array[count($array) - 1]));

            $newValue = new Calc_Value($digitalValue, $uncertainty);

            // Charge chaque membre
            $members = [];
            foreach ($membersRef as $memberRef) {
                $memberRef = trim($memberRef);
                $member = null;
                foreach ($dimensions as $dimension) {
                    try {
                        $member = $dimension->getMember($memberRef);
                        break;
                    } catch (Core_Exception_NotFound $e) {
                    }
                }
                if (! $member) {
                    UI_Message::addMessageStatic(__('Parameter', 'import', 'unknownMember', ['MEMBER' => $memberRef]));
                    $this->redirect('parameter/family/edit/id/' . $family->getId());
                    return;
                }
                $members[] = $member;
            }

            // Définit la valeur
            $family->getCell($members)->setValue($newValue);

            $number++;
        }

        $this->entityManager->flush();

        UI_Message::addMessageStatic(__('UI', 'message', 'updated'), UI_Message::TYPE_SUCCESS);
        $this->redirect('parameter/family/edit/id/' . $family->getId());
    }
}
