<?php

use Core\Annotation\Secure;
use Parameter\Domain\Family\Dimension;
use Parameter\Domain\Family\Member;

/**
 * @author matthieu.napoli
 */
class Parameter_Datagrid_MemberDatagridController extends UI_Controller_Datagrid
{
    /**
     * @Secure("editParameterFamily")
     */
    public function getelementsAction()
    {
        /** @var $dimension Dimension */
        $dimension = Dimension::load($this->getParam('idDimension'));
        $members = $dimension->getMembers();

        foreach ($members as $member) {
            $data = [];
            $data['index'] = $member->getId();
            $data['label'] = $this->cellTranslatedText($member->getLabel());
            $data['ref'] = $member->getRef();
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
     * @Secure("editParameterFamily")
     */
    public function updateelementAction()
    {
        /** @var $member Member */
        $member = Member::load($this->update['index']);
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
            case 'ref':
                $member->setRef($newValue);
                $this->data = $newValue;
                break;
            case 'label':
                $this->translationHelper->set($member->getLabel(), $newValue);
                $this->data = $newValue;
                break;
        }
        $member->save();
        $this->entityManager->flush();
        $this->message = __('UI', 'message', 'updated');
        $this->send();
    }

    /**
     * @Secure("editParameterFamily")
     */
    public function deleteelementAction()
    {
        /** @var $member Member */
        $member = Member::load($this->getParam('index'));
        $dimension = $member->getDimension();
        $dimension->removeMember($member);
        $dimension->save();
        $this->entityManager->flush();
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }
}
