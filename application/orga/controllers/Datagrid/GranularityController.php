<?php

use Core\Annotation\Secure;
use Core\Work\ServiceCall\ServiceCallTask;
use MyCLabs\Work\Dispatcher\SynchronousWorkDispatcher;

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
     * @Secure("viewOrganization")
     */
    public function getelementsAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));

        try {
            $granularityForinventoryStatus = $organization->getGranularityForInventoryStatus();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $granularityForinventoryStatus = null;
        }

        $this->request->filter->addCondition(Orga_Model_Granularity::QUERY_ORGANIZATION, $organization);
        $this->request->order->addOrder(Orga_Model_Granularity::QUERY_POSITION);
        /**@var Orga_Model_Granularity $granularity */
        foreach (Orga_Model_Granularity::loadList($this->request) as $granularity) {
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
            $data['inventory'] = ($granularity === $granularityForinventoryStatus);
            $data['reports'] = $granularity->getCellsGenerateDWCubes();
            $data['acl'] = $granularity->getCellsWithACL();
            if ((!$granularity->hasAxes()) || $data['relevance'] || $data['input']
                || $data['afs'] || $data['reports'] || $data['acl'] || $data['inventory']) {
                $data['delete'] = false;
            }
            if (!$data['input']) {
                $this->editableCell($data['input'], false);
            }
            $this->addLine($data);
        }
        $this->send();
    }

    /**
     * @Secure("editOrganization")
     */
    public function addelementAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));

        $refAxes = $this->getAddElementValue('axes');
        if (empty($refAxes)) {
            $this->setAddElementErrorMessage('axes', __('Orga', 'granularity', 'emptyGranularity'), true);
        } else {
            $axes = [];
            $refGranularity = '';
            foreach ($refAxes as $refAxis) {
                $refGranularity .= $refAxis . '|';
                $axis = $organization->getAxisByRef($refAxis);
                // On regarde si les axes précédement ajouter ne sont pas lié hierachiquement à l'axe actuel.
                if (!$axis->isTransverse($axes)) {
                    $this->setAddElementErrorMessage('axes', __('Orga', 'granularity', 'hierarchicallyLinkedAxes'), true);
                    break;
                } else {
                    $axes[] = $axis;
                }
            }
            $refGranularity = substr($refGranularity, 0, -1);

            try {
                $organization->getGranularityByRef($refGranularity);
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
                'Orga_Service_OrganizationService',
                'addGranularity',
                [
                    $organization,
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
     * @Secure("editOrganization")
     */
    public function deleteelementAction()
    {
        $granularity = Orga_Model_Granularity::load($this->delete);
        try {
            $granularityForInventoryStatus =  $granularity->getOrganization()->getGranularityForInventoryStatus();
            if ($granularityForInventoryStatus === $granularity) {
                throw new Core_Exception_User('Orga', 'granularity', 'granularityCantBeDeleted');
            }
        } catch (Core_Exception_UndefinedAttribute $e) {
            // Pas de granularité des inventares.
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
            'Orga_Service_OrganizationService',
            'removeGranularity',
            [$granularity],
            __('Orga', 'backgroundTasks', 'removeGranularity', ['LABEL' => $granularity->getLabel()])
        );
        $this->workDispatcher->runAndWait($task, $this->waitDelay, $success, $timeout, $error);

        $this->send();
    }

    /**
     * @Secure("editOrganization")
     */
    public function updateelementAction()
    {
        $granularity = Orga_Model_Granularity::load($this->update['index']);

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
                'Orga_Service_OrganizationService',
                'editGranularity',
                [$granularity, [ $this->update['column'] => (bool) $this->update['value'] ]],
                __('Orga', 'backgroundTasks', 'editGranularity', ['LABEL' => $granularity->getLabel()])
            );
            $this->workDispatcher->runAndWait($task, $this->waitDelay, $success, $timeout, $error);
        } else {
            $granularity->setInputConfigGranularity();
            $granularity->save();
            $this->data = ['value' => $granularity->isInput(), 'editable' => false];
        }

        $this->send();
    }
}
