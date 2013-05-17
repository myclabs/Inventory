<?php
/**
 * @author  matthieu.napoli
 * @package AF
 */

use Core\Annotation\Secure;

/**
 * @package AF
 */
class AF_Datagrid_Edit_Algos_NumericParameter_CoordinatesAlgoController extends UI_Controller_Datagrid
{

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::getelementsAction()
     * @Secure("editAF")
     */
    public function getelementsAction()
    {
        /** @var $algo Algo_Model_Numeric_Parameter */
        $algo = Algo_Model_Numeric_Parameter::load($this->_getParam('idAlgo'));
        $coordinates = $algo->getParameterCoordinates();
        foreach ($coordinates as $coordinate) {
            if ($coordinate instanceof Algo_Model_ParameterCoordinate_Algo) {
                $data = [];
                $data['index'] = $coordinate->getId();
                try {
                    $data['dimension'] = $coordinate->getDimension()->getId();
                } catch (Core_Exception_NotFound $e) {
                    // Si la dimension n'existe plus
                    $data['dimension'] = $this->cellList(null, __('AF', 'configTreatmentInvalidRef', 'dimension'));
                }
                $data['algo'] = $coordinate->getAlgoKeyword()->getId();
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
        /** @var $af AF_Model_AF */
        $af = AF_Model_AF::load($this->_getParam('id'));
        /** @var $algo Algo_Model_Numeric_Parameter */
        $algo = Algo_Model_Numeric_Parameter::load($this->_getParam('idAlgo'));
        $idDimension = $this->getAddElementValue('dimension');
        if (empty($idDimension)) {
            $this->setAddElementErrorMessage('dimension', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        $idAlgoKeyword = $this->getAddElementValue('algo');
        if (empty($idAlgoKeyword)) {
            $this->setAddElementErrorMessage('algo', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        // Pas d'erreurs
        if (empty($this->_addErrorMessages)) {
            /** @var $dimension Techno_Model_Family_Dimension */
            $dimension = Techno_Model_Family_Dimension::load($idDimension);
            /** @var $algoKeyword Algo_Model_Selection_TextKey */
            $algoKeyword = Algo_Model_Selection_TextKey::load($idAlgoKeyword);
            $coordinate = new Algo_Model_ParameterCoordinate_Algo();
            /** @noinspection PhpUndefinedVariableInspection */
            $coordinate->setDimension($dimension);
            $coordinate->setAlgoKeyword($algoKeyword);
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
        /** @var $coordinate Algo_Model_ParameterCoordinate_Algo */
        $coordinate = Algo_Model_ParameterCoordinate_Algo::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'algo':
                if (empty($newValue)) {
                    throw new Core_Exception_User('UI', 'formValidation', 'emptyRequiredField');
                }
                /** @var $algoKeyword Algo_Model_Selection_TextKey */
                $algoKeyword = Algo_Model_Selection_TextKey::load($newValue);
                $coordinate->setAlgoKeyword($algoKeyword);
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
        $algo = Algo_Model_Numeric_Parameter::load($this->_getParam('idAlgo'));
        /** @var $coordinate Algo_Model_ParameterCoordinate_Algo */
        $coordinate = Algo_Model_ParameterCoordinate_Algo::load($this->_getParam('index'));
        $coordinate->delete();
        $algo->removeParameterCoordinates($coordinate);
        $algo->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

}
