<?php
/**
 * @author valentin.claras
 * @author sidoine.tardieu
 * @package Orga
 */

use Core\Annotation\Secure;
use DI\Annotation\Inject;
use MyCLabs\Work\Dispatcher\WorkDispatcher;

/**
 * Datagrid de granularity
 * @package Orga
 */
class Orga_Datagrid_Cell_GranularitydataproviderController extends UI_Controller_Datagrid
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
     * @Secure("editOrganization")
     */
    public function getelementsAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));
        $this->request->filter->addCondition(Orga_Model_Granularity::QUERY_ORGANIZATION, $organization);
        $this->request->order->addOrder(Orga_Model_Granularity::QUERY_POSITION);
        foreach (Orga_Model_Granularity::loadList($this->request) as $granularity) {
            /** @var Orga_Model_Granularity $granularity */
            $data = array();
            $data['index'] = $granularity->getRef();
            $data['label'] = $granularity->getLabel();
            $data['cellsWithACL'] = $granularity->getCellsWithACL();
            $data['cellsGenerateDWCube'] = $granularity->getCellsGenerateDWCubes();
            $data['cellsWithOrgaTab'] = $granularity->getCellsWithOrgaTab();
            $data['cellsWithAFConfigTab'] = $granularity->getCellsWithAFConfigTab();
            $data['cellsWithSocialGenericActions'] = $granularity->getCellsWithSocialGenericActions();
            $data['cellsWithSocialContextActions'] = $granularity->getCellsWithSocialContextActions();
            $data['cellsWithInputDocs'] = $granularity->getCellsWithInputDocs();
            $this->addLine($data);
        }
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
     * @Secure("editOrganization")
     */
    public function updateelementAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));
        $granularity = Orga_Model_Granularity::loadByRefAndOrganization($this->update['index'], $organization);

        switch ($this->update['column']) {
            case 'cellsWithACL':
                $granularity->setCellsWithACL((bool) $this->update['value']);
                $this->data = $granularity->getCellsWithACL();
                break;
            case 'cellsGenerateDWCube':
                $success = function() {
                    $this->message = __('UI', 'message', 'updated');
                };
                $timeout = function() {
                    $this->message = __('UI', 'message', 'updatedLater');
                };
                $error = function() {
                    throw new Core_Exception("Error in the background task");
                };

                // Lance la tache en arrière plan
                $task = new Orga_Work_Task_SetGranularityCellsGenerateDWCubes(
                    $granularity,
                    $this->update['value']
                );
                $this->workDispatcher->runBackground($task, $this->waitDelay, $success, $timeout, $error);

                $this->data = $granularity->getCellsGenerateDWCubes();
                $this->send();
                return;
            case 'cellsWithOrgaTab':
                $granularity->setCellsWithOrgaTab((bool) $this->update['value']);
                $this->data = $granularity->getCellsWithOrgaTab();
                break;
            case 'cellsWithAFConfigTab':
                $granularity->setCellsWithAFConfigTab((bool) $this->update['value']);
                $this->data = $granularity->getCellsWithAFConfigTab();
                break;
            case 'cellsWithSocialGenericActions':
                $granularity->setCellsWithSocialGenericActions((bool) $this->update['value']);
                $this->data = $granularity->getCellsWithSocialGenericActions();
                break;
            case 'cellsWithSocialContextActions':
                $granularity->setCellsWithSocialContextActions((bool) $this->update['value']);
                $this->data = $granularity->getCellsWithSocialContextActions();
                break;
            case 'cellsWithInputDocs':
                $granularity->setCellsWithInputDocuments((bool) $this->update['value']);
                $this->data = $granularity->getCellsWithInputDocuments();
                break;
            default:
                parent::updateelementAction();
                break;
        }
        $this->message = __('UI', 'message', 'updated', array('GRANULARITY' => $granularity->getLabel()));

        $this->send();
    }
}