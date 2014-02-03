<?php
/**
 * @author  matthieu.napoli
 * @package AF
 */

use AF\Domain\Algorithm\Numeric\NumericParameterAlgo;
use AF\Domain\Algorithm\ParameterCoordinate\FixedParameterCoordinate;
use Core\Annotation\Secure;
use Techno\Domain\Family\Dimension;

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
        /** @var $algo NumericParameterAlgo */
        $algo = NumericParameterAlgo::load($this->getParam('idAlgo'));
        $coordinates = $algo->getParameterCoordinates();
        foreach ($coordinates as $coordinate) {
            if ($coordinate instanceof FixedParameterCoordinate) {
                $data = [];
                $data['index'] = $coordinate->getId();
                try {
                    $data['dimension'] = $coordinate->getDimension()->getId();
                } catch (Core_Exception_NotFound $e) {
                    // Si la dimension n'existe plus
                    $data['dimension'] = $this->cellList(null, __('AF', 'configTreatmentInvalidRef', 'dimension'));
                }
                try {
                    $data['member'] = $coordinate->getMember();
                } catch (Core_Exception_UndefinedAttribute $e) {
                    $data['member'] = null;
                }
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
        /** @var $algo NumericParameterAlgo */
        $algo = NumericParameterAlgo::load($this->getParam('idAlgo'));
        $idDimension = $this->getAddElementValue('dimension');
        if (empty($idDimension)) {
            $this->setAddElementErrorMessage('dimension', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        // Pas d'erreurs
        if (empty($this->_addErrorMessages)) {
            /** @var $dimension Dimension */
            $dimension = Dimension::load($idDimension);
            $coordinate = new FixedParameterCoordinate();
            $coordinate->setDimensionRef($dimension->getRef());
            $coordinate->save();
            $algo->addParameterCoordinates($coordinate);
            $algo->save();
            $this->entityManager->flush();
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
        /** @var $coordinate FixedParameterCoordinate */
        $coordinate = FixedParameterCoordinate::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'member':
                if (empty($newValue)) {
                    throw new Core_Exception_User('UI', 'formValidation', 'emptyRequiredField');
                }
                $member = $coordinate->getDimension()->getMember($newValue);
                $coordinate->setMember($member->getRef());
                $this->data = $newValue;
                break;
        }
        $coordinate->save();
        $this->entityManager->flush();
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
        /** @var $algo NumericParameterAlgo */
        $algo = NumericParameterAlgo::load($this->getParam('idAlgo'));
        /** @var $coordinate FixedParameterCoordinate */
        $coordinate = FixedParameterCoordinate::load($this->getParam('index'));
        $coordinate->delete();
        $algo->removeParameterCoordinates($coordinate);
        $algo->save();
        $this->entityManager->flush();
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

    /**
     * Renvoie la liste des membres d'une dimension
     * @Secure("editAF")
     */
    public function getMemberListAction()
    {
        /** @var $coordinate FixedParameterCoordinate */
        $coordinate = FixedParameterCoordinate::load($this->getParam('index'));

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
