<?php

use Core\Annotation\Secure;
use Techno\Domain\Family\Family;
use Techno\Domain\Family\Dimension;
use Techno\Domain\Meaning;

/**
 * @author matthieu.napoli
 */
class Techno_Datagrid_Family_DimensionsDatagridController extends UI_Controller_Datagrid
{
    /**
     * @Secure("editTechno")
     */
    public function getelementsAction()
    {
        /** @var $family Family */
        $family = Family::load($this->getParam('idFamily'));
        $dimensions = $family->getDimensions();

        foreach ($dimensions as $dimension) {
            $data = [];
            $data['index'] = $dimension->getId();
            $data['orientation'] = $dimension->getOrientation();
            $data['meaning'] = $dimension->getMeaning()->getId();
            // $data['query'] = $dimension->getQuery();
            $data['members'] = implode(', ', $dimension->getMembers()->toArray());
            $data['details'] = $this->_helper->url('details', 'dimension', 'techno',
                                                   ['id' => $dimension->getId()]);
            $canMoveUp = ($dimension->getPosition() > 1);
            $canMoveDown = ($dimension->getPosition() < $dimension->getLastEligiblePosition());
            $data['position'] = $this->cellPosition($dimension->getPosition(), $canMoveUp, $canMoveDown);
            $this->addLine($data);
        }

        $this->totalElements = count($dimensions);
        $this->send();
    }

    /**
     * @Secure("editTechno")
     */
    public function addelementAction()
    {
        /** @var $family Family */
        $family = Family::load($this->getParam('idFamily'));
        // Validation du formulaire
        $orientation = $this->getAddElementValue('orientation');
        if (empty($orientation)) {
            $this->setAddElementErrorMessage('orientation', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        $idMeaning = $this->getAddElementValue('meaning');
        if (empty($idMeaning)) {
            $this->setAddElementErrorMessage('meaning', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        // Pas d'erreurs
        if (empty($this->_addErrorMessages)) {
            $meaning = Meaning::load($idMeaning);
            try {
                $dimension = new Dimension($family, $meaning, $orientation);
                $dimension->save();
                $family->addDimension($dimension);
                $family->save();
                $this->entityManager->flush();
            } catch (Core_ORM_DuplicateEntryException $e) {
                $this->setAddElementErrorMessage('meaning', __('Techno', 'familyDetail', 'meaningAlreadyUsed'));
                $this->send();
                return;
            }
            $this->message = __('UI', 'message', 'added');
        }
        $this->send();
    }

    /**
     * @Secure("editTechno")
     */
    public function updateelementAction()
    {
        /** @var $dimension Dimension */
        $dimension = Dimension::load($this->update['index']);
        $newValue = $this->update['value'];
        switch($this->update['column']) {
            case 'orientation':
                $dimension->setOrientation($newValue);
                break;
            case 'position':
                $oldPosition = $dimension->getPosition();
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
                        $newPosition = $dimension->getLastEligiblePosition();
                        break;
                    default:
                        $newPosition = $newValue;
                        break;
                }
                $dimension->setPosition($newPosition);
                break;
        }
        $dimension->save();
        $this->entityManager->flush();
        $this->message = __('UI', 'message', 'updated');
        $this->send();
    }

    /**
     * @Secure("editTechno")
     */
    public function deleteelementAction()
    {
        /** @var $dimension Dimension */
        $dimension = Dimension::load($this->getParam('index'));
        $family = $dimension->getFamily();
        $family->removeDimension($dimension);
        $family->save();
        $this->entityManager->flush();
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }
}
