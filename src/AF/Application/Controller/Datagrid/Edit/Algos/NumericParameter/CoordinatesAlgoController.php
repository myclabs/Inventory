<?php

use AF\Domain\AF;
use AF\Domain\Algorithm\Numeric\NumericParameterAlgo;
use AF\Domain\Algorithm\ParameterCoordinate\AlgoParameterCoordinate;
use AF\Domain\Algorithm\Selection\TextKeySelectionAlgo;
use Core\Annotation\Secure;
use Parameter\Domain\Family\Dimension;

/**
 * @author matthieu.napoli
 */
class AF_Datagrid_Edit_Algos_NumericParameter_CoordinatesAlgoController extends UI_Controller_Datagrid
{
    /**
     * @Secure("editAF")
     */
    public function getelementsAction()
    {
        /** @var $algo NumericParameterAlgo */
        $algo = NumericParameterAlgo::load($this->getParam('idAlgo'));
        $coordinates = $algo->getParameterCoordinates();
        foreach ($coordinates as $coordinate) {
            if ($coordinate instanceof AlgoParameterCoordinate) {
                $data = [];
                $data['index'] = $coordinate->getId();
                try {
                    $data['dimension'] = $coordinate->getDimension()->getId();
                } catch (Core_Exception_NotFound $e) {
                    // Si la dimension n'existe plus
                    $data['dimension'] = $this->cellList(null, __('AF', 'configTreatmentInvalidRef', 'dimension'));
                }
                $data['algo'] = $coordinate->getSelectionAlgo()->getId();
                $this->addLine($data);
            }
        }
        $this->send();
    }

    /**
     * @Secure("editAF")
     */
    public function addelementAction()
    {
        /** @var $af AF */
        $af = AF::load($this->getParam('id'));
        /** @var $algo NumericParameterAlgo */
        $algo = NumericParameterAlgo::load($this->getParam('idAlgo'));
        $idDimension = $this->getAddElementValue('dimension');
        if (empty($idDimension)) {
            $this->setAddElementErrorMessage('dimension', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        $idSelectionAlgo = $this->getAddElementValue('algo');
        if (empty($idSelectionAlgo)) {
            $this->setAddElementErrorMessage('algo', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        // Pas d'erreurs
        if (empty($this->_addErrorMessages)) {
            /** @var $dimension Dimension */
            $dimension = Dimension::load($idDimension);
            /** @var $selectionAlgo TextKeySelectionAlgo */
            $selectionAlgo = TextKeySelectionAlgo::load($idSelectionAlgo);
            $coordinate = new AlgoParameterCoordinate();
            /** @noinspection PhpUndefinedVariableInspection */
            $coordinate->setDimensionRef($dimension->getRef());
            $coordinate->setSelectionAlgo($selectionAlgo);
            $coordinate->save();
            $algo->addParameterCoordinates($coordinate);
            $algo->save();
            $this->entityManager->flush();
            $this->message = __('UI', 'message', 'added');
        }
        $this->send();
    }

    /**
     * @Secure("editAF")
     */
    public function updateelementAction()
    {
        /** @var $coordinate AlgoParameterCoordinate */
        $coordinate = AlgoParameterCoordinate::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'algo':
                if (empty($newValue)) {
                    throw new Core_Exception_User('UI', 'formValidation', 'emptyRequiredField');
                }
                /** @var $selectionAlgo TextKeySelectionAlgo */
                $selectionAlgo = TextKeySelectionAlgo::load($newValue);
                $coordinate->setSelectionAlgo($selectionAlgo);
                $this->data = $newValue;
                break;
        }
        $coordinate->save();
        $this->entityManager->flush();
        $this->message = __('UI', 'message', 'updated');
        $this->send();
    }

    /**
     * @Secure("editAF")
     */
    public function deleteelementAction()
    {
        /** @var $algo NumericParameterAlgo */
        $algo = NumericParameterAlgo::load($this->getParam('idAlgo'));
        /** @var $coordinate AlgoParameterCoordinate */
        $coordinate = AlgoParameterCoordinate::load($this->getParam('index'));
        $coordinate->delete();
        $algo->removeParameterCoordinates($coordinate);
        $algo->save();
        $this->entityManager->flush();
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }
}
