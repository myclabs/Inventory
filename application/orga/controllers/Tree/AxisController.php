<?php

use Core\Annotation\Secure;

/**
 * @author valentin.claras
 */
class Orga_Tree_AxisController extends UI_Controller_Tree
{
    /**
     * @Secure("viewOrganization")
     */
    public function getnodesAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));
        if ($this->idNode === null) {
            $axes = $organization->getRootAxes();
        } else {
            $currentAxis = $organization->getAxisByRef($this->idNode);
            $axes = $currentAxis->getDirectBroaders();
        }
        foreach ($axes as $axis) {
            $this->addNode(
                $axis->getRef(),
               '<b>'.$this->translator->get($axis->getLabel()).'</b> <i>('.$axis->getRef().')</i>',
               (!$axis->hasDirectBroaders()),
                null,
                false,
                true,
                true
            );
        }

        $this->send();
    }

    /**
     * @Secure("editOrganization")
     */
    public function addnodeAction()
    {
        /** @var Orga_Model_Organization $organization */
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));

        $axisRef = $this->getAddElementValue('addAxis_ref');
        try {
            Core_Tools::checkRef($axisRef);
        } catch (Core_Exception_User $e) {
            $this->setAddFormElementErrorMessage('addAxis_ref', $e->getMessage());
        }
        try {
            $existingAxis = $organization->getAxisByRef($this->getAddElementValue('addAxis_ref'));
            $this->setAddFormElementErrorMessage('addAxis_ref', __('UI', 'formValidation', 'alreadyUsedIdentifier'));
        } catch (Core_Exception_NotFound $e) {
            // La référence n'est pas utilisée.
        }

        if (empty($this->_formErrorMessages)) {
            if ($this->getAddElementValue('addAxis_parent') != null) {
                $narrower = $organization->getAxisByRef($this->getAddElementValue('addAxis_parent'));
                $axis = new Orga_Model_Axis($organization, $axisRef, $narrower);
            } else {
                $axis = new Orga_Model_Axis($organization, $axisRef);
            }
            $this->translator->set($axis->getLabel(), $this->getAddElementValue('addAxis_label'));
            if ($this->getAddElementValue('addAxis_isContextualizing') === 'contextualizing') {
                $axis->setContextualize(true);
            } else {
                $axis->setContextualize(false);
            }
            $axis->save();

            if ($axis->getDirectNarrower() === null) {
                $this->message = __('UI', 'message', 'added', [
                    'AXIS' => $this->translator->get($axis->getLabel())
                ]);
            } else {
                $this->message = __('UI', 'message', 'added', [
                    'AXIS' => $this->translator->get($axis->getLabel()),
                    'PARENT' => $this->translator->get($axis->getDirectNarrower()->getLabel()),
                ]);
            }
        }

        $this->send();
    }

    /**
     * @Secure("viewOrganization")
     */
    public function getlistparentsAction()
    {
        $this->addElementList(null, '');

        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));
        foreach ($organization->getFirstOrderedAxes() as $axis) {
            $this->addElementList($axis->getRef(), ' '.$this->translator->get($axis->getLabel()));
        }

        $this->send();
    }

    /**
     * @Secure("viewOrganization")
     */
    public function getlistsiblingsAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));
        $axis = $organization->getAxisByRef($this->idNode);

        if ($axis->getDirectNarrower() === null) {
            $axes = $organization->getRootAxes();
        } else {
            $axes = $axis->getDirectNarrower()->getDirectBroaders();
        }
        foreach ($axes as $siblingAxis) {
            if ($siblingAxis !== $axis) {
                $this->addElementList(
                    $siblingAxis->getRef(),
                    $this->translator->get($siblingAxis->getLabel()).' ('.$siblingAxis->getRef().')'
                );
            }
        }

        $this->send();
    }

    /**
     * @Secure("editOrganization")
     */
    public function editnodeAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));
        $axis = $organization->getAxisByRef($this->idNode);

        $newRef = $this->getEditElementValue('ref');
        $newLabel = $this->getEditElementValue('label');

        try {
            Core_Tools::checkRef($newRef);
        } catch (Core_Exception_User $e) {
            $this->setEditFormElementErrorMessage('ref', $e->getMessage());
        }

        if ($this->getEditElementValue('isContextualizing') === 'contextualizing') {
            $contextualizing = true;
        } else {
            $contextualizing = false;
        }
        switch ($this->getEditElementValue('changeOrder')) {
            case 'first':
                $newPosition = 1;
                break;
            case 'last':
                $newPosition = $axis->getLastEligiblePosition();
                break;
            case 'after':
                $currentAxisPosition = $axis->getPosition();
                $refAfter = $this->getEditElementValue('selectAfter');
                $newPosition = $organization->getAxisByRef($refAfter)->getPosition();
                if (($currentAxisPosition > $newPosition)) {
                    $newPosition += 1;
                }
                break;
            default:
                $newPosition = null;
                break;
        }

        if ($newRef !== $this->idNode) {
            try {
                $existingAxis = $organization->getAxisByRef($newRef);
                $this->setEditFormElementErrorMessage('ref', __('UI', 'formValidation', 'alreadyUsedIdentifier'));
            } catch (Core_Exception_NotFound $e) {
                // La référence n'est pas utilisée.
            }
        }

        if (empty($this->_formErrorMessages)) {
            if ($axis->getRef() !== $newRef) {
                $axis->setRef($newRef);
            }
            if ($this->translator->get($axis->getLabel()) !== $newLabel) {
                $this->translator->set($axis->getLabel(), $newLabel);
            }
            if ($axis->isContextualizing() !== $contextualizing) {
                try {
                    $axis->setContextualize($contextualizing);
                } catch (Core_Exception_TooMany $e) {
                    throw new Core_Exception_User('Orga', 'exceptions', 'test');
                }
            }
            if (($newPosition !== null) && ($axis->getPosition() !== $newPosition)) {
                $axis->setPosition($newPosition);
            }
            $this->message = __('UI', 'message', 'updated', [
                'AXIS' => $this->translator->get($axis->getLabel())
            ]);
        }

        $this->entityManager->beginTransaction();
        try {
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Core_Exception_TooMany $e) {
            $this->entityManager->rollback();
            $this->entityManager->clear();
            $this->setEditFormElementErrorMessage(
                'isContextualizing',
                __('Orga', 'axis', 'contextualizingAxisHasMembersWithSameRef')
            );
        }

        $this->send();
    }

    /**
     * @Secure("editOrganization")
     */
    public function deletenodeAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));
        $axis = $organization->getAxisByRef($this->idNode);

        if ($axis->hasGranularities()) {
            throw new Core_Exception_User('Orga', 'axis', 'axisHasGranularities',
                array('AXIS' => $this->getParam('label')));
        } else if ($axis->hasMembers()) {
            throw new Core_Exception_User('Orga', 'axis', 'axisHasMembers',
                    array('AXIS' => $this->getParam('label')));
        } else if ($axis->hasDirectBroaders()) {
            throw new Core_Exception_User('Orga', 'axis', 'axisHasDirectBroaders',
                    array('AXIS' => $this->getParam('label')));
        }
        $axis->delete();
        $this->message = __('UI', 'message', 'deleted', [
            'AXIS' => $this->translator->get($axis->getLabel())
        ]);

        $this->send();
    }

    /**
     * @Secure("editOrganization")
     */
    public function getinfoeditAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));
        $axis = $organization->getAxisByRef($this->idNode);

        $this->data['ref'] = $axis->getRef();
        $this->data['label'] = $this->translator->get($axis->getLabel());
        $this->data['isContextualizing'] = $axis->isContextualizing() ? 'contextualizing' : 'notContextualizing';

        $this->send();
    }
}
