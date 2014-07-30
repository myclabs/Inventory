<?php

use Core\Annotation\Secure;
use Core\Work\ServiceCall\ServiceCallTask;
use MyCLabs\Work\Dispatcher\SynchronousWorkDispatcher;
use Orga\Domain\Axis;
use Orga\Domain\Workspace;
use Orga\Application\Service\Workspace\WorkspaceService;

/**
 * @author valentin.claras
 */
class Orga_Tree_AxisController extends UI_Controller_Tree
{

    /**
     * @Inject
     * @var SynchronousWorkDispatcher
     */
    private $workDispatcher;

    /**
     * @Inject("work.waitDelay")
     * @var int
     */
    private $waitDelay;

    /**
     * @Secure("viewWorkspace")
     */
    public function getnodesAction()
    {
        $workspace = Workspace::load($this->getParam('workspace'));
        if ($this->idNode === null) {
            $axes = $workspace->getRootAxes();
        } else {
            $currentAxis = $workspace->getAxisByRef($this->idNode);
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
     * @Secure("editWorkspace")
     */
    public function addnodeAction()
    {
        /** @var Workspace $workspace */
        $workspace = Workspace::load($this->getParam('workspace'));

        $axisRef = $this->getAddElementValue('addAxis_ref');
        try {
            Core_Tools::checkRef($axisRef);
        } catch (Core_Exception_User $e) {
            $this->setAddFormElementErrorMessage('addAxis_ref', $e->getMessage());
        }
        try {
            $existingAxis = $workspace->getAxisByRef($this->getAddElementValue('addAxis_ref'));
            $this->setAddFormElementErrorMessage('addAxis_ref', __('UI', 'formValidation', 'alreadyUsedIdentifier'));
        } catch (Core_Exception_NotFound $e) {
            // La référence n'est pas utilisée.
        }

        if (empty($this->_formErrorMessages)) {
            if ($this->getAddElementValue('addAxis_parent') != null) {
                $narrower = $workspace->getAxisByRef($this->getAddElementValue('addAxis_parent'));
                $axis = new Axis($workspace, $axisRef, $narrower);
            } else {
                $axis = new Axis($workspace, $axisRef);
            }
            $this->translator->set($axis->getLabel(), $this->getAddElementValue('addAxis_label'));
            if ($this->getAddElementValue('addAxis_isContextualizing') === 'contextualizing') {
                $axis->setContextualize(true);
            } else {
                $axis->setContextualize(false);
            }
            if ($this->getAddElementValue('addAxis_isPositioning') === 'positioning') {
                $axis->setMemberPositioning(true);
            } else {
                $axis->setMemberPositioning(false);
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
     * @Secure("viewWorkspace")
     */
    public function getlistparentsAction()
    {
        $this->addElementList(null, '');

        $workspace = Workspace::load($this->getParam('workspace'));
        foreach ($workspace->getFirstOrderedAxes() as $axis) {
            $this->addElementList($axis->getRef(), ' '.$this->translator->get($axis->getLabel()));
        }

        $this->send();
    }

    /**
     * @Secure("viewWorkspace")
     */
    public function getlistsiblingsAction()
    {
        $workspace = Workspace::load($this->getParam('workspace'));
        $axis = $workspace->getAxisByRef($this->idNode);

        if ($axis->getDirectNarrower() === null) {
            $axes = $workspace->getRootAxes();
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
     * @Secure("editWorkspace")
     */
    public function editnodeAction()
    {
        $workspace = Workspace::load($this->getParam('workspace'));
        $axis = $workspace->getAxisByRef($this->idNode);

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

        if ($this->getEditElementValue('isPositioning') === 'positioning') {
            $positioning = true;
        } else {
            $positioning = false;
            if ($axis->isMemberPositioning() && ($workspace->getTimeAxis() === $axis)) {
                $this->setEditFormElementErrorMessage('isPositioning', ___('Orga', 'axis', 'positioningAxisIsTimeAxis'));
            }
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
                $newPosition = $workspace->getAxisByRef($refAfter)->getPosition();
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
                $existingAxis = $workspace->getAxisByRef($newRef);
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
                $axis->setContextualize($contextualizing);
            }
            if ($axis->isMemberPositioning() !== $positioning) {
                $axis->setMemberPositioning($positioning);
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
     * @Secure("editWorkspace")
     */
    public function deletenodeAction()
    {
        $workspace = Workspace::load($this->getParam('workspace'));
        $axis = $workspace->getAxisByRef($this->idNode);

        if ($axis->hasGranularities()) {
            throw new Core_Exception_User('Orga', 'axis', 'axisHasGranularities',
                ['AXIS' => $this->getParam('label')]);
        } else if ($axis->hasMembers()) {
            throw new Core_Exception_User('Orga', 'axis', 'axisHasMembers',
                    ['AXIS' => $this->getParam('label')]);
        } else if ($axis->hasDirectBroaders()) {
            throw new Core_Exception_User('Orga', 'axis', 'axisHasDirectBroaders',
                    ['AXIS' => $this->getParam('label')]);
        }

        $this->entityManager->flush();

        $success = function () {
            $this->message = __('UI', 'message', 'deleted');
        };
        $timeout = function () {
            $this->message = __('UI', 'message', 'deletedLater');
        };
        $error = function (Exception $e) {
            throw $e;
        };

        // Lance la tache en arrière plan
        $task = new ServiceCallTask(
            WorkspaceService::class,
            'deleteAxis',
            [$axis],
            __('Orga', 'backgroundTasks', 'deleteAxis', [
                    'AXIS' => $this->translator->get($axis->getLabel()),
            ])
        );
        $this->workDispatcher->runAndWait($task, $this->waitDelay, $success, $timeout, $error);


        $this->message = __('UI', 'message', 'deleted', [
            'AXIS' => $this->translator->get($axis->getLabel())
        ]);

        $this->send();
    }

    /**
     * @Secure("editWorkspace")
     */
    public function getinfoeditAction()
    {
        $workspace = Workspace::load($this->getParam('workspace'));
        $axis = $workspace->getAxisByRef($this->idNode);

        $this->data['ref'] = $axis->getRef();
        $this->data['label'] = $this->translator->get($axis->getLabel());
        $this->data['isContextualizing'] = $axis->isContextualizing() ? 'contextualizing' : 'notContextualizing';
        $this->data['isPositioning'] = $axis->isMemberPositioning() ? 'positioning' : 'notPositionning';

        $this->send();
    }
}
