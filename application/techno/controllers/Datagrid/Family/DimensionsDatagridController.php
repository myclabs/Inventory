<?php
/**
 * @author matthieu.napoli
 * @package Techno
 */

use Core\Annotation\Secure;

/**
 * @package Techno
 */
class Techno_Datagrid_Family_DimensionsDatagridController extends UI_Controller_Datagrid
{

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::getelementsAction()
     * @Secure("editTechno")
     */
    public function getelementsAction()
    {
        /** @var $family Techno_Model_Family */
        $family = Techno_Model_Family::load($this->_getParam('idFamily'));
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
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::addelementAction()
     * @Secure("editTechno")
     */
    public function addelementAction()
    {
        /** @var $family Techno_Model_Family */
        $family = Techno_Model_Family::load($this->_getParam('idFamily'));
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
            $meaning = Techno_Model_Meaning::load($idMeaning);
            $dimension = new Techno_Model_Family_Dimension($family, $meaning, $orientation);
            $dimension->save();
            $family->addDimension($dimension);
            $family->save();
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
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
        /** @var $dimension Techno_Model_Family_Dimension */
        $dimension = Techno_Model_Family_Dimension::load($this->update['index']);
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
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
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
        /** @var $dimension Techno_Model_Family_Dimension */
        $dimension = Techno_Model_Family_Dimension::load($this->_getParam('index'));
        $family = $dimension->getFamily();
        $family->removeDimension($dimension);
        $family->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

}
