<?php
/**
 * Classe Orga_Datagrid_RelevantController
 * @author valentin.claras
 * @author sidoine.tardieu
 * @package Orga
 */

use Core\Annotation\Secure;
use MyCLabs\Work\Dispatcher\WorkDispatcher;
use Core\Work\ServiceCall\ServiceCallTask;

/**
 * Controller des datagrid des cellules
 * @package Orga
 */
class Orga_Datagrid_Organization_RelevanceController extends UI_Controller_Datagrid
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
     * Fonction renvoyant la liste des éléments peuplant la Datagrid.
     *
     * Récupération des paramètres de tris et filtres de la manière suivante :
     *  $this->request.
     *
     * Récupération des arguments de la manière suivante :
     *  $this->getParam('nomArgument').
     *
     * Renvoie la liste d'éléments, le nombre total et un message optionnel.
     *
     * @Secure("editOrganizationAndCells")
     */
    public function getelementsAction()
    {
        /** @var User_Model_User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $this->request->setCustomParameters($this->request->filter->getConditions());
        $this->request->filter->setConditions(array());
        $this->request->filter->addCondition(Orga_Model_Cell::QUERY_ALLPARENTSRELEVANT, true);
//        $this->request->aclFilter->enabled = true;
//        $this->request->aclFilter->user = $connectedUser;
//        $this->request->aclFilter->action = User_Model_Action_Default::EDIT();

        $cell = Orga_Model_Cell::load($this->getParam('idCell'));
        $granularity = Orga_Model_Granularity::load($this->getParam('idGranularity'));

        $this->request->order->addOrder(Orga_Model_Cell::QUERY_TAG);
        foreach ($cell->loadChildCellsForGranularity($granularity, $this->request) as $childCell) {
            $data = array();
            $data['index'] = $childCell->getId();
            foreach ($childCell->getMembers() as $member) {
                $data[$member->getAxis()->getRef()] = $member->getCompleteRef();
            }
            $data['relevant'] = $childCell->getRelevant();
            $this->addLine($data);
        }
        $this->totalElements = $cell->countTotalChildCellsForGranularity($granularity, $this->request);

        $this->send();
    }

    /**
     * Fonction modifiant un élément.
     *
     * Récupération de la ligne à modifier de la manière suivante :
     *  $this->update['index'].
     *
     * Récupération de la colonne à modifier de la manière suivante :
     *  $this->update['column'].
     *
     * Récupération de la nouvelle valeur à modifier de la manière suivante :
     *  $this->update['value'].
     *
     * Récupération des arguments de la manière suivante :
     *  $this->getParam('nomArgument').
     *
     * Renvoie un message d'information et la nouvelle donnée à afficher dans la cellule.
     *
     * @Secure("editOrganizationAndCells")
     */
    function updateelementAction()
    {
        if ($this->update['column'] !== 'relevant') {
            parent::updateelementAction();
        }

        $childCell = Orga_Model_Cell::load($this->update['index']);

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
            [$childCell, (bool) $this->update['value']],
            __('Orga', 'backgroundTasks', 'setCellRelevance', ['LABEL' => $childCell->getLabel()])
        );
        $this->workDispatcher->runBackground($task, $this->waitDelay, $success, $timeout, $error);

        $this->send();
    }

}
