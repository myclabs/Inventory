<?php

use Core\Annotation\Secure;
use Techno\Domain\Family\Dimension;
use Techno\Domain\Family\Member;

/**
 * @author matthieu.napoli
 */
class Techno_Datagrid_MemberDatagridController extends UI_Controller_Datagrid
{
    /**
     * @Secure("editTechno")
     */
    public function getelementsAction()
    {
        /** @var $dimension Dimension */
        $dimension = Dimension::load($this->getParam('id'));
        $members = $dimension->getMembers();

        foreach ($members as $member) {
            $data = [];
            $data['index'] = $member->getId();
            $data['label'] = $member->getLabel();
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
     * @Secure("editTechno")
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
                $member->setLabel($newValue);
                $this->data = $newValue;
                break;
        }
        $member->save();
        $this->entityManager->flush();
        $this->message = __('UI', 'message', 'updated');
        $this->send();
    }

    /**
     * @Secure("editTechno")
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
