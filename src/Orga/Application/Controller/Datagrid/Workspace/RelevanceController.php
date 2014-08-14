<?php

use Core\Annotation\Secure;
use MyCLabs\Work\Dispatcher\SynchronousWorkDispatcher;
use Orga\Application\Service\Cell\CellService;
use Orga\Domain\Cell;
use Orga\Domain\Granularity;
use User\Domain\ACL\Actions;
use Core\Work\ServiceCall\ServiceCallTask;
use User\Domain\User;

/**
 * @author valentin.claras
 */
class Orga_Datagrid_Workspace_RelevanceController extends UI_Controller_Datagrid
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
     * @Secure("editWorkspaceAndCells")
     */
    public function getelementsAction()
    {
        /** @var User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $granularityId = $this->getParam('granularity');
        /** @var Granularity $granularity */
        $granularity = Granularity::load($granularityId);

        $this->request->filter->addCondition(Cell::QUERY_ALLPARENTSRELEVANT, true);
        $this->request->filter->addCondition(Cell::QUERY_GRANULARITY, $granularity);
        $this->request->aclFilter->enabled = true;
        $this->request->aclFilter->user = $connectedUser;
        $this->request->aclFilter->action = Actions::EDIT;

        $this->request->order->addOrder(Cell::QUERY_TAG);
        /** @var Cell $cell */
        foreach (Cell::loadList($this->request) as $cell) {
            $data = [];
            $data['index'] = $cell->getId();
            foreach ($cell->getMembers() as $member) {
                $data[$member->getAxis()->getRef()] = $member->getTag();
            }
            $data['relevant'] = $cell->getRelevant();
            $this->addLine($data);
        }
        $this->totalElements = Cell::countTotal($this->request);

        $this->send();
    }

    /**
     * @Secure("editWorkspaceAndCells")
     */
    public function updateelementAction()
    {
        if ($this->update['column'] !== 'relevant') {
            parent::updateelementAction();
        }

        $cell = Cell::load($this->update['index']);

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
            CellService::class,
            'setCellRelevance',
            [$cell, (bool) $this->update['value']],
            __('Orga', 'backgroundTasks', 'setCellRelevance', [
                'CELL' => $this->translator->get($cell->getExtendedLabel())
            ])
        );
        $this->workDispatcher->runAndWait($task, $this->waitDelay, $success, $timeout, $error);

        $this->send();
    }
}
