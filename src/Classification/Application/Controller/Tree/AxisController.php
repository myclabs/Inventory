<?php

use Classification\Application\Service\AxisService;
use Classification\Domain\Axis;
use Classification\Domain\ClassificationLibrary;
use Core\Annotation\Secure;
use DI\Annotation\Inject;

class Classification_Tree_AxisController extends UI_Controller_Tree
{
    /**
     * @Inject
     * @var AxisService
     */
    private $axisService;

    /**
     * @Secure("editClassificationLibrary")
     */
    public function getnodesAction()
    {
        /** @var ClassificationLibrary $library */
        $library = ClassificationLibrary::load($this->getParam('library'));

        if ($this->idNode === null) {
            $axes = $library->getRootAxes();
        } else {
            $axes = Axis::load($this->idNode)->getDirectBroaders();
        }
        foreach ($axes as $axis) {
            $axisLabel = '<b>' . $this->translator->toString($axis->getLabel()) . '</b> <i>('.$axis->getRef().')</i>';
            $this->addNode($axis->getId(), $axisLabel, (!$axis->hasdirectBroaders()), null, false, true, true);
        }

        $this->send();
    }

    /**
     * @Secure("editClassificationLibrary")
     */
    public function addnodeAction()
    {
        /** @var ClassificationLibrary $library */
        $library = ClassificationLibrary::load($this->getParam('library'));

        $ref = $this->getAddElementValue('ref');
        $label = $this->getAddElementValue('label');
        $idParent = $this->getAddElementValue('parent');

        $refErrors = $this->axisService->getErrorMessageForNewRef($library, $ref);
        if ($refErrors != null) {
            $this->setAddFormElementErrorMessage('ref', $refErrors);
        }

        if (empty($this->_formErrorMessages)) {
            $this->axisService->add($library, $ref, $label, $idParent);
            $this->message = __('UI', 'message', 'added');
        }

        $this->send();
    }

    /**
     * @Secure("editClassificationLibrary")
     */
    public function editnodeAction()
    {
        /** @var ClassificationLibrary $library */
        $library = ClassificationLibrary::load($this->getParam('library'));

        $axis = Axis::load($this->idNode);
        $newLabel = $this->getEditElementValue('label');
        $newRef = $this->getEditElementValue('ref');
        $newParentId = $this->getEditElementValue('changeParent');
        if ($newParentId != 0) {
            $newParentId = ($newParentId === ($this->id.'_root')) ? null : $newParentId;
        }
        switch ($this->getEditElementValue('changeOrder')) {
            case 'first':
                $newPosition = 1;
                break;
            case 'last':
                if ($newParentId == 0) {
                    $newPosition = $axis->getLastEligiblePosition();
                } elseif ($newParentId === null) {
                    $queryRootAxis = new Core_Model_Query();
                    $queryRootAxis->filter->addCondition(Axis::QUERY_NARROWER, null, Core_Model_Filter::OPERATOR_NULL);
                    $newPosition = Axis::countTotal($queryRootAxis) + 1;
                } else {
                    $newPosition = count($axis->getDirectBroaders()) + 1;
                }
                break;
            case 'after':
                $refAfter = $this->getEditElementValue('selectAfter');
                $currentAxisPosition = $axis->getPosition();
                $newPosition = Axis::load($refAfter)->getPosition();
                if (($newParentId != 0) || ($currentAxisPosition > $newPosition)) {
                    $newPosition += 1;
                }
                break;
            default:
                $newPosition = null;
                break;
        }

        if ($newRef !== $axis->getRef()) {
            $refErrors = $this->axisService->getErrorMessageForNewRef($library, $newRef);
            if ($refErrors != null) {
                $this->setEditFormElementErrorMessage('ref', $refErrors);
            }
        }

        if (empty($this->_formErrorMessages)) {
            $label = null;
            $currentAxisLabel = $this->translator->toString($axis->getLabel());
            if (($axis->getRef() !== $newRef) && ($currentAxisLabel !== $newLabel)) {
                $label = $this->axisService->updateRefAndLabel($axis->getId(), $newRef, $newLabel);
            } elseif ($currentAxisLabel !== $newLabel) {
                $label = $this->axisService->updateLabel($axis->getId(), $newLabel);
            } elseif ($axis->getRef() !== $newRef) {
                $label = $this->axisService->updateRef($axis->getId(), $newRef);
            }
            if ($newParentId != 0) {
                $label = $this->axisService->updateParent($axis->getId(), $newParentId, $newPosition);
            } elseif (($newPosition !== null) && ($axis->getPosition() !== $newPosition)) {
                $label = $this->axisService->updatePosition($axis->getId(), $newPosition);
            }
            if ($label !== null) {
                $this->message = __('UI', 'message', 'updated');
            } else {
                $this->message = __('UI', 'message', 'nullUpdated');
            }
        }

        $this->send();
    }

    /**
     * @Secure("editClassificationLibrary")
     */
    public function getlistparentsAction()
    {
        /** @var ClassificationLibrary $library */
        $library = ClassificationLibrary::load($this->getParam('library'));

        $this->addElementList(0, '');
        if (($this->idNode !== null) && (Axis::load($this->idNode)->getDirectNarrower() !== null)) {
            $this->addElementList($this->id.'_root', __('Classification', 'axis', 'rootParentAxisLabel'));
        }

        foreach ($library->getAxes()->toArray() as $axis) {
            /** @var Axis $axis */
            if ($axis->getId() != $this->idNode) {
                $this->addElementList($axis->getId(), $this->translator->toString($axis->getLabel()));
            }
        }
        $this->send();
    }

    /**
     * @Secure("editClassificationLibrary")
     */
    public function getlistsiblingsAction()
    {
        /** @var ClassificationLibrary $library */
        $library = ClassificationLibrary::load($this->getParam('library'));

        $axis = Axis::load($this->idNode);
        if (($this->getParam('idParent') != null) && ($this->getParam('idParent') !== $this->id.'_root')) {
            $axisParent = Axis::load($this->getParam('idParent'));
            $siblingAxes = $axisParent->getDirectBroaders();
        } elseif (($axis->getDirectNarrower() === null) || ($this->getParam('idParent') === $this->id.'_root')) {
            $siblingAxes = $library->getRootAxes();
        } else {
            $siblingAxes = $axis->getDirectNarrower()->getDirectBroaders();
        }

        foreach ($siblingAxes as $siblingAxis) {
            if ($siblingAxis->getId() != $this->idNode) {
                $this->addElementList(
                    $siblingAxis->getId(),
                    $this->translator->toString($siblingAxis->getLabel())
                );
            }
        }

        $this->send();
    }

    /**
     * @Secure("editClassificationLibrary")
     */
    public function getinfoeditAction()
    {
        $axis = Axis::load($this->idNode);
        $this->data['ref'] = $axis->getRef();
        $this->data['label'] = $this->translator->toString($axis->getLabel());
        $this->send();
    }

    /**
     * @Secure("editClassificationLibrary")
     */
    public function deletenodeAction()
    {
        $this->axisService->delete($this->idNode);

        $this->message = __('UI', 'message', 'deleted');

        $this->send();
    }

}
