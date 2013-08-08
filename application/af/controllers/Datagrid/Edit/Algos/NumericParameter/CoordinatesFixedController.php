<?php
/**
 * @author  matthieu.napoli
 * @package AF
 */

use Core\Annotation\Secure;

/**
 * @package AF
 */
class AF_Datagrid_Edit_Algos_NumericParameter_CoordinatesFixedController extends UI_Controller_Datagrid
{

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::getelementsAction()
     * @Secure("editAF")
     */
    public function getelementsAction()
    {
        /** @var $algo Algo_Model_Numeric_Parameter */
        $algo = Algo_Model_Numeric_Parameter::load($this->getParam('idAlgo'));
        $coordinates = $algo->getParameterCoordinates();
        foreach ($coordinates as $coordinate) {
            if ($coordinate instanceof Algo_Model_ParameterCoordinate_Fixed) {
                $data = [];
                $data['index'] = $coordinate->getId();
                try {
                    $data['dimension'] = $coordinate->getDimension()->getId();
                } catch (Core_Exception_NotFound $e) {
                    // Si la dimension n'existe plus
                    $data['dimension'] = $this->cellList(null, __('AF', 'configTreatmentInvalidRef', 'dimension'));
                }
                $data['member'] = $coordinate->getMemberKeywordRef();
                $this->addLine($data);
            }
        }
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::addelementAction()
     * @Secure("editAF")
     */
    public function addelementAction()
    {
        /** @var $algo Algo_Model_Numeric_Parameter */
        $algo = Algo_Model_Numeric_Parameter::load($this->getParam('idAlgo'));
        $idDimension = $this->getAddElementValue('dimension');
        if (empty($idDimension)) {
            $this->setAddElementErrorMessage('dimension', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        // Pas d'erreurs
        if (empty($this->_addErrorMessages)) {
            /** @var $dimension Techno_Model_Family_Dimension */
            $dimension = Techno_Model_Family_Dimension::load($idDimension);
            $coordinate = new Algo_Model_ParameterCoordinate_Fixed();
            $coordinate->setDimension($dimension);
            $coordinate->save();
            $algo->addParameterCoordinates($coordinate);
            $algo->save();
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
            $this->message = __('UI', 'message', 'added');
        }
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::updateelementAction()
     * @Secure("editAF")
     */
    public function updateelementAction()
    {
        /** @var $algo Algo_Model_Numeric_Parameter */
        $algo = Algo_Model_Numeric_Parameter::load($this->getParam('idAlgo'));
        /** @var $coordinate Algo_Model_ParameterCoordinate_Fixed */
        $coordinate = Algo_Model_ParameterCoordinate_Fixed::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'member':
                if (empty($newValue)) {
                    throw new Core_Exception_User('UI', 'formValidation', 'emptyRequiredField');
                }
                /** @var $member Techno_Model_Family_Member */
                $keyword = Keyword_Model_Keyword::loadByRef($newValue);
                $member = $coordinate->getDimension()->getMember($keyword);
                $coordinate->setMember($member);
                $this->data = $newValue;
                break;
        }
        $coordinate->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->message = __('UI', 'message', 'updated');
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::deleteelementAction()
     * @Secure("editAF")
     */
    public function deleteelementAction()
    {
        /** @var $algo Algo_Model_Numeric_Parameter */
        $algo = Algo_Model_Numeric_Parameter::load($this->getParam('idAlgo'));
        /** @var $coordinate Algo_Model_ParameterCoordinate_Fixed */
        $coordinate = Algo_Model_ParameterCoordinate_Fixed::load($this->getParam('index'));
        $coordinate->delete();
        $algo->removeParameterCoordinates($coordinate);
        $algo->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

    /**
     * Renvoie la liste des membres d'une dimension
     * @Secure("editAF")
     */
    public function getMemberListAction()
    {
        /** @var $coordinate Algo_Model_ParameterCoordinate_Fixed */
        $coordinate = Algo_Model_ParameterCoordinate_Fixed::load($this->getParam('index'));

        try {
            $dimension = $coordinate->getDimension();
        } catch (Core_Exception_NotFound $e) {
            $this->send();
            return;
        }

        foreach ($dimension->getMembers() as $member) {
            $this->addElementList($member->getRef(), $member->getLabel());
        }
        $this->send();
    }

}
