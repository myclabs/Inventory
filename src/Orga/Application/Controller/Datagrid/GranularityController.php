<?php

use Core\Annotation\Secure;
use Core\Work\ServiceCall\ServiceCallTask;
use MyCLabs\Work\Dispatcher\SynchronousWorkDispatcher;
use Orga\Application\Service\Workspace\WorkspaceService;
use Orga\Domain\Granularity;
use Orga\Domain\Axis;
use Orga\Domain\Workspace;

/**
 * @author valentin.claras
 */
class Orga_Datagrid_GranularityController extends UI_Controller_Datagrid
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
    public function getelementsAction()
    {
        $workspace = Workspace::load($this->getParam('workspace'));

        $granularityForInventoryStatus = $workspace->getGranularityForInventoryStatus();

        $this->request->filter->addCondition(Granularity::QUERY_WORKSPACE, $workspace);
        $this->request->order->addOrder(Granularity::QUERY_POSITION);
        /**@var Granularity $granularity */
        foreach (Granularity::loadList($this->request) as $granularity) {
            $data = [];
            $data['index'] = $granularity->getId();
            $listAxes = [];
            foreach ($granularity->getAxes() as $axis) {
                $listAxes[] = $axis->getRef();
            }
            $data['axes'] = $this->cellList($listAxes);
            $data['relevance'] = $granularity->getCellsControlRelevance();
            $data['input'] = $granularity->isInput();
            $data['afs'] = $granularity->hasInputGranularities();
            $data['inventory'] = (($granularity === $granularityForInventoryStatus) ?
                $this->cellList('monitoring', ___('Orga', 'inventory', 'editing')) :
                ($granularity->getCellsMonitorInventory() ?
                    'monitoring' :
                    'none'
                )
            );
            $data['reports'] = $granularity->getCellsGenerateDWCubes();
            $data['acl'] = $granularity->getCellsWithACL();
            if ((!$granularity->hasAxes()) || $data['relevance'] || $data['input']
                || $data['afs'] || $data['reports'] || $data['acl'] || ($data['inventory'] != 'none')) {
                $data['delete'] = false;
            }
            if (!$data['input']) {
                $this->editableCell($data['input'], false);
            }
            if ($granularity === $granularityForInventoryStatus) {
                $this->editableCell($data['inventory'], false);
            }
            $this->addLine($data);
        }
        $this->send();
    }

    /**
     * @Secure("editWorkspace")
     */
    public function addelementAction()
    {
        $workspace = Workspace::load($this->getParam('workspace'));

        $axesRefs = $this->getAddElementValue('axes');
        if (empty($axesRefs)) {
            $this->setAddElementErrorMessage('axes', __('Orga', 'granularity', 'emptyGranularity'), true);
        } else {
            /** @var Axis[] $axes */
            $axes = [];
            $granularityRef = '';
            foreach ($axesRefs as $axisRef) {
                $granularityRef .= $axisRef . '|';
                $axis = $workspace->getAxisByRef($axisRef);
                // On regarde si les axes précédement ajouter ne sont pas lié hierachiquement à l'axe actuel.
                if (!$axis->isTransverse($axes)) {
                    $this->setAddElementErrorMessage('axes', __('Orga', 'granularity', 'hierarchicallyLinkedAxes'), true);
                    break;
                } else {
                    $axes[] = $axis;
                }
            }
            $granularityRef = substr($granularityRef, 0, -1);

            try {
                $workspace->getGranularityByRef($granularityRef);
                $this->setAddElementErrorMessage('axes', __('Orga', 'granularity', 'granularityAlreadyExists'), true);
            } catch (Core_Exception_NotFound $e) {
                // La granularité n'existe pas déjà.
            }
        }

        if (empty($this->_addErrorMessages)) {
            $success = function () {
                $this->message = __('UI', 'message', 'added');
            };
            $timeout = function () {
                $this->message = __('UI', 'message', 'addedLater');
            };
            $error = function (Exception $e) {
                throw $e;
            };

            // Lance la tache en arrière plan
            $task = new ServiceCallTask(
                WorkspaceService::class,
                'addGranularity',
                [
                    $workspace,
                    $axes,
                    [
                        'relevance' => (bool) $this->getAddElementValue('relevance'),
                        'reports'   => (bool) $this->getAddElementValue('reports'),
                        'acl'       => (bool) $this->getAddElementValue('acl')
                    ]
                ],
                __('Orga', 'backgroundTasks', 'addGranularity', ['LABEL' => implode(', ', $axes)])
            );
            $this->workDispatcher->runAndWait($task, $this->waitDelay, $success, $timeout, $error);
        }

        $this->send();
    }

    /**
     * @Secure("editWorkspace")
     */
    public function deleteelementAction()
    {
        $granularity = Granularity::load($this->delete);
        $granularityForInventoryStatus =  $granularity->getWorkspace()->getGranularityForInventoryStatus();
        if ($granularityForInventoryStatus === $granularity) {
            throw new Core_Exception_User('Orga', 'granularity', 'granularityCantBeDeleted');
        }

        if ($granularity->getCellsWithACL() || $granularity->isInput() || $granularity->getCellsControlRelevance()
            || $granularity->hasInputGranularities() || $granularity->getCellsGenerateDWCubes()) {
            throw new Core_Exception_User('Orga', 'granularity', 'granularityCantBeDeleted');
        }

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
            'removeGranularity',
            [$granularity],
            __('Orga', 'backgroundTasks', 'removeGranularity', [
                'LABEL' => $this->translator->get($granularity->getLabel())
            ])
        );
        $this->workDispatcher->runAndWait($task, $this->waitDelay, $success, $timeout, $error);

        $this->send();
    }

    /**
     * @Secure("editWorkspace")
     */
    public function updateelementAction()
    {
        $granularity = Granularity::load($this->update['index']);

        if ($this->update['column'] !== 'input') {
            if ($this->update['column'] === 'acl') {
                if (!$this->update['value']) {
                    foreach ($granularity->getCells() as $cell) {
                        if (count($cell->getAllRoles()) > 0) {
                            throw new Core_Exception_User('Orga', 'granularity', 'roleExistsForCellAtThisGranularity');
                        }
                    }
                }
            }
            if ($this->update['column'] === 'inventory') {
                $value = ($this->update['value'] == 'monitoring');
            } else {
                $value = (bool) $this->update['value'];
            }

            $success = function () {
                $this->message = __('UI', 'message', 'updated');
            };
            $timeout = function () {
                $this->message = __('UI', 'message', 'updatedLater');
            };
            $error = function (Exception $e) {
                throw $e;
            };
            // Lance la tache en arrière plan
            $task = new ServiceCallTask(
                WorkspaceService::class,
                'editGranularity',
                [$granularity, [ $this->update['column'] => $value ]],
                __('Orga', 'backgroundTasks', 'editGranularity', [
                    'LABEL' => $this->translator->get($granularity->getLabel())
                ])
            );
            $this->workDispatcher->runAndWait($task, $this->waitDelay, $success, $timeout, $error);
        } else {
            $granularity->setInputConfigGranularity();
            $granularity->save();
            $this->message = __('UI', 'message', 'updated');
            $this->data = ['value' => $granularity->isInput(), 'editable' => false];
        }

        $this->send();
    }
}
