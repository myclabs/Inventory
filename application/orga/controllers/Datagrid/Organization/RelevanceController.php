<?php

use Core\Annotation\Secure;
use MyCLabs\Work\Dispatcher\SynchronousWorkDispatcher;
use User\Domain\ACL\Actions;
use Core\Work\ServiceCall\ServiceCallTask;
use User\Domain\User;

/**
 * @author valentin.claras
 */
class Orga_Datagrid_Organization_RelevanceController extends UI_Controller_Datagrid
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
     * @Secure("editOrganizationAndCells")
     */
    public function getelementsAction()
    {
        /** @var User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $idGranularity = $this->getParam('idGranularity');
        /** @var Orga_Model_Granularity $granularity */
        $granularity = Orga_Model_Granularity::load($idGranularity);

        $this->request->filter->addCondition(Orga_Model_Cell::QUERY_ALLPARENTSRELEVANT, true);
        $this->request->filter->addCondition(Orga_Model_Cell::QUERY_GRANULARITY, $granularity);
        $this->request->aclFilter->enabled = true;
        $this->request->aclFilter->user = $connectedUser;
        $this->request->aclFilter->action = Actions::EDIT;

        $this->request->order->addOrder(Orga_Model_Cell::QUERY_TAG);
        /** @var Orga_Model_Cell $cell */
        foreach (Orga_Model_Cell::loadList($this->request) as $cell) {
            $data = [];
            $data['index'] = $cell->getId();
            foreach ($cell->getMembers() as $member) {
                $data[$member->getAxis()->getRef()] = $member->getTag();
            }
            $data['relevant'] = $cell->getRelevant();
            $this->addLine($data);
        }
        $this->totalElements = Orga_Model_Cell::countTotal($this->request);

        $this->send();
    }

    /**
     * @Secure("editOrganizationAndCells")
     */
    public function updateelementAction()
    {
        if ($this->update['column'] !== 'relevant') {
            parent::updateelementAction();
        }

        $cell = Orga_Model_Cell::load($this->update['index']);

        // worker.
        $success = function () {
            $this->message = __('UI', 'message', 'updated');
        };
        $timeout = function () {
            $this->message = __('UI', 'message', 'updatedLater');
        };
        $error = function (Exception $e) {
            throw $e;
        };

        $task = new ServiceCallTask(
            'Orga_Service_CellService',
            'setCellRelevance',
            [$cell, (bool) $this->update['value']],
            __('Orga', 'backgroundTasks', 'setCellRelevance', [
                'LABEL' => $this->translator->toString($cell->getLabel())
            ])
        );
        $this->workDispatcher->runAndWait($task, $this->waitDelay, $success, $timeout, $error);

        $this->send();
    }
}
