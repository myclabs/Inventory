<?php

use Core\Annotation\Secure;
use MyCLabs\Work\Dispatcher\WorkDispatcher;
use User\Domain\ACL\Role\Role;

/**
 * @author valentin.claras
 */
class Orga_Datagrid_GranularityController extends UI_Controller_Datagrid
{
    /**
     * @Inject
     * @var WorkDispatcher
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
            $data['afs'] = $granularity->isInput();
            if (!$data['afs']) {
                $this->editableCell($data['afs'], false);
            }
            $data['reports'] = $granularity->getCellsGenerateDWCubes();
            $data['acl'] = $granularity->getCellsWithACL();
            if (!($granularity->hasAxes())) {
                $data['delete'] = false;
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
        $axes = array();
        $refGranularity = '';
        if (empty($refAxes)) {
            $this->setAddElementErrorMessage('axes', __('Orga', 'granularity', 'emptyGranularity'));
        }

        foreach ($refAxes as $refAxis) {
            $refGranularity .= $refAxis . '|';
            $axis = $organization->getAxisByRef($refAxis);
            // On regarde si les axes précédement ajouter ne sont pas lié hierachiquement à l'axe actuel.
            if (!$axis->isTransverse($axes)) {
                $this->setAddElementErrorMessage('axes', __('Orga', 'granularity', 'hierarchicallyLinkedAxes'));
                break;
            } else {
                $axes[] = $axis;
            }
        }
        $refGranularity = substr($refGranularity, 0, -1);

        try {
            $organization->getGranularityByRef($refGranularity);
            $this->setAddElementErrorMessage('axes', __('Orga', 'granularity', 'granularityAlreadyExists'));
        } catch (Core_Exception_NotFound $e) {
            // La granularité n'existe pas déjà.
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
            $task = new \Core\Work\ServiceCall\ServiceCallTask(
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
            $this->workDispatcher->runBackground($task, $this->waitDelay, $success, $timeout, $error);
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

        if ($granularity->getCellsWithACL()) {
            throw new Core_Exception_User('Orga', 'granularity', 'granularityCantBeDeleted');
        }

        $granularity->delete();

        $this->entityManager->flush();

        $this->message = __('UI', 'message', 'deleted', array('GRANULARITY' => $granularity->getLabel()));

        $this->send();
    }

    /**
     * @Secure("editOrganization")
     */
    public function updateelementAction()
    {
        $granularity = Orga_Model_Granularity::load($this->update['index']);

        switch ($this->update['column']) {
            case 'relevance':
                $granularity->setCellsControlRelevance((bool) $this->update['value']);
                $this->data = $granularity->getCellsControlRelevance();
                break;
            case 'acl':
                if (!$this->update['value']) {
                    foreach ($granularity->getCells() as $cell) {
                        if (count($cell->getAllRoles()) > 0) {
                            throw new Core_Exception_User('Orga', 'granularity', 'roleExistsForCellAtThisGranularity');
                        }
                    }
                }
                $granularity->setCellsWithACL((bool) $this->update['value']);
                $this->data = $granularity->getCellsWithACL();
                break;
            case 'afs':
                $granularity->setInputConfigGranularity();
                $this->data = ['value' => $granularity->isInput(), 'editable' => false];
                break;
            case 'reports':
                $granularity->setCellsGenerateDWCubes((bool) $this->update['value']);
                $this->data = $granularity->getCellsGenerateDWCubes();
                break;
            default:
                parent::updateelementAction();
                break;
        }
        $granularity->save();
        $this->message = __('UI', 'message', 'updated', array('GRANULARITY' => $granularity->getLabel()));

        $this->send();
    }
}
